<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Bowl;

use Closure;
use Exception;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use RuntimeException;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Recipe\Ingredient\TransformableInterface;

use function class_exists;
use function current;
use function is_array;
use function is_object;
use function is_string;
use function next;
use function reset;

/**
 * Default base implementation for Bowl to manage parameter mapping with the workplan's ingredients.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait BowlTrait
{
    /**
     * Name of the action
     */
    private string $name;

    /**
     * To map some argument's name to another ingredient name on the workplan.
     * @var array<string, string|string[]>
     */
    private array $mapping = [];

    /**
     * To cache the reflections about parameters of the callable
     * @var array<string, ReflectionParameter>
     */
    private ?array $parametersCache = null;

    /**
     * @var array<string, ReflectionClass<object>>
     */
    private static array $reflectionsClasses = [];

    /**
     * @var array<string, array<string, ReflectionMethod>>
     */
    private static array $reflectionsMethods = [];

    /**
     * @var array<string, ReflectionFunction>
     */
    private static array $reflectionsFunctions = [];

    /**
     * @var array<string, ReflectionMethod>
     */
    private static array $reflectionsInvokables = [];

    /**
     * @var array<string, array<ReflectionParameter>>
     */
    private static array $reflectionsParameters = [];

    /**
     * @param class-string $objectOrClass
     * @return ReflectionClass<object>
     */
    private static function getReflectionClass(string $objectOrClass): ReflectionClass
    {
        $getter = static function () use ($objectOrClass): ReflectionClass {
            return static::$reflectionsClasses[$objectOrClass] = new ReflectionClass($objectOrClass);
        };

        return static::$reflectionsClasses[$objectOrClass] ?? $getter();
    }

    /**
     * @param object|class-string $objectOrClass
     */
    private static function getReflectionMethod(object|string $objectOrClass, string $methodName): ReflectionMethod
    {
        if (is_object($objectOrClass)) {
            $objectOrClass = $objectOrClass::class;
        }

        $getter = static function () use ($objectOrClass, $methodName): ReflectionMethod {
            $reflectionClass = static::getReflectionClass($objectOrClass);

            return static::$reflectionsMethods[$objectOrClass][$methodName] = $reflectionClass->getMethod($methodName);
        };

        return static::$reflectionsMethods[$objectOrClass][$methodName] ?? $getter();
    }

    private static function getReflectionFunction(string $function): ReflectionFunction
    {
        $getter = static function () use ($function): ReflectionFunction {
            return static::$reflectionsFunctions[$function] = new ReflectionFunction($function);
        };

        return static::$reflectionsFunctions[$function] ?? $getter();
    }

    private static function getReflectionInvokable(object $invokable): ReflectionMethod
    {
        $invokableClass = $invokable::class;

        $getter = static function () use ($invokableClass): ReflectionMethod {
            $reflectionClass = new ReflectionClass($invokableClass);

            return static::$reflectionsInvokables[$invokableClass] = $reflectionClass->getMethod('__invoke');
        };

        return static::$reflectionsInvokables[$invokableClass] ?? $getter();
    }

    /**
     * To return the Reflection instance about this callable, supports functions, closures, objects methods or class
     * methods.
     *
     * @throws ReflectionException
     */
    private static function getReflection(callable $callable): ReflectionFunctionAbstract
    {
        if (is_array($callable)) {
            //The callable is checked by PHP in the constructor by the type hitting
            return static::getReflectionMethod($callable[0], $callable[1]);
        }

        if ($callable instanceof Closure) {
            return new ReflectionFunction($callable);
        }

        if (is_string($callable)) {
            return static::getReflectionFunction($callable);
        }

        //It's not a closure, so it's mandatory a invokable object (because the callable is valid)
        return static::getReflectionInvokable((object) $callable);
    }

    /**
     * To extract the list of ReflectionParameter instances about the current callable.
     *
     * @return array<string, ReflectionParameter>|ReflectionParameter[]
     * @throws ReflectionException
     */
    private function listParameters(callable $callable): array
    {
        $reflection = static::getReflection($callable);
        $oid = $reflection->getFileName() . ':' . $reflection->getStartLine();

        $getter = static function () use ($oid, $reflection): array {
            $parameters = [];
            foreach ($reflection->getParameters() as $parameter) {
                $parameters[$parameter->getName()] = $parameter;
            }

            return static::$reflectionsParameters[$oid] = $parameters;
        };

        return static::$reflectionsParameters[$oid] ?? $getter();
    }

    /**
     * @param array<string, mixed> $workPlan
     * @param array<mixed> $values
     */
    private function findInstanceForParameter(
        ReflectionParameter $parameter,
        array &$workPlan,
        array &$values,
        bool $allowTransform,
        ?string $transformClassName
    ): bool {
        $automaticValueFound = false;

        $refClass = null;
        if ($allowTransform && null !== $transformClassName && class_exists($transformClassName)) {
            $refClass = static::getReflectionClass($transformClassName);
        }

        foreach ($workPlan as &$variable) {
            if (!is_object($variable)) {
                continue;
            }

            if ($this->isInstanceOf($parameter, $variable)) {
                $values[] = $variable;

                $automaticValueFound = true;
                break;
            }

            if (
                $allowTransform
                && null !== $refClass
                && $variable instanceof TransformableInterface
                && $refClass->isInstance($variable)
            ) {
                $values[] = $variable->transform();

                $automaticValueFound = true;
                break;
            }
        }

        return $automaticValueFound;
    }

    private function isInstanceOf(ReflectionParameter $parameter, object $instance): bool
    {
        $type = $parameter->getType();
        if (!$type instanceof ReflectionUnionType && !$type instanceof ReflectionNamedType) {
            return false;
        }

        $checkType = static function (ReflectionNamedType $type) use ($parameter, $instance): bool {
            if ($type->isBuiltin()) {
                return false;
            }

            $className = $type->getName();
            if ('self' === $className) {
                return $parameter->getDeclaringClass()->isInstance($instance);
            }

            $rfClass = new ReflectionClass($className);
            return $rfClass->isInstance($instance);
        };

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $subType) {
                if ($subType instanceof ReflectionNamedType && $checkType($subType)) {
                    return true;
                }
            }

            return false;
        }

        return $checkType($type);
    }

    /**
     * To map each callable's arguments to ingredients available into the workplan.
     *
     * @param array<string, mixed> $workPlan
     *
     * @return array<mixed>
     *
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function extractParameters(callable $callable, ChefInterface $chef, array &$workPlan): array
    {
        $values = [];
        foreach ($this->listParameters($callable) as $name => $parameter) {
            if ($this->isInstanceOf($parameter, $chef)) {
                $values[] = $chef;
                continue;
            }

            //Check if we must transform an ingredient before put it into the bowl
            $allowTransform = false;
            $transformClassName = null;
            if (!empty($attributes = $parameter->getAttributes(Transform::class))) {
                /** @var Transform $attr */
                $attr = $attributes[0]->newInstance();
                $transformClassName = $attr->getClassName();
                $allowTransform = true;
            }

            if (!empty($this->mapping[$name])) {
                $mapping = $this->mapping[$name];
                if (is_string($mapping)) {
                    $name = $mapping;
                } elseif (is_array($mapping)) {
                    reset($mapping);
                    do {
                        $name = current($mapping);
                    } while (!isset($workPlan[$name]) && next($mapping));
                }
            }

            //Pick the good value from the workplan
            $skip = false;
            $tempValue = match (true) {
                //Found from ingredient's name in workplan
                isset($workPlan[$name]) => $workPlan[$name],

                //Found from ingredient's class in workplan
                ($type = $parameter->getType()) instanceof ReflectionNamedType
                    && isset($workPlan[$type->getName()]) => $workPlan[$type->getName()],

                //Found from ingredient's instance type
                $this->findInstanceForParameter(
                    $parameter,
                    $workPlan,
                    $values,
                    $allowTransform,
                    $transformClassName
                ) => $skip = true,

                //Special name `_methodName`
                BowlInterface::METHOD_NAME === $name => $this->name,

                //Not found, if it is not optional, throw an exception
                !$parameter->isOptional() => throw new RuntimeException(
                    "Missing the parameter {$parameter->getName()} ({$name}) in the WorkPlan"
                ),

                //Return the default value
                default => $parameter->getDefaultValue(),
            };

            if (true === $skip) {
                continue;
            }

            if ($allowTransform && $tempValue instanceof TransformableInterface) {
                $values[] = $tempValue->transform();
            } else {
                $values[] = $tempValue;
            }
        }

        return $values;
    }
}
