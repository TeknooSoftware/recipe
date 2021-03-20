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
     * @var array<string, array<string, ReflectionMethod>>
     */
    private static array $reflectionsClasses = [];

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
     * @param object|class-string $objectOrClass
     */
    private static function getReflectionClass($objectOrClass, string $methodName): ReflectionMethod
    {
        if (is_object($objectOrClass)) {
            $objectOrClass = $objectOrClass::class;
        }

        $getter = static function () use ($objectOrClass, $methodName): ReflectionMethod {
            $reflectionClass = new ReflectionClass($objectOrClass);

            return static::$reflectionsClasses[$objectOrClass][$methodName] = $reflectionClass->getMethod($methodName);
        };

        return static::$reflectionsClasses[$objectOrClass][$methodName] ?? $getter();
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
            return static::getReflectionClass($callable[0], $callable[1]);
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
     * @return array<string, ReflectionParameter>
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
    private function findInstanceForParameter(ReflectionParameter $parameter, array &$workPlan, array &$values): bool
    {
        $automaticValueFound = false;

        foreach ($workPlan as &$variable) {
            if (is_object($variable) && $this->isInstanceOf($parameter, $variable)) {
                $values[] = $variable;
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

            if (isset($workPlan[$name])) {
                $values[] = $workPlan[$name];
                continue;
            }

            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && isset($workPlan[$type->getName()])) {
                $values[] = $workPlan[$type->getName()];
                continue;
            }

            $automaticValueFound = $this->findInstanceForParameter($parameter, $workPlan, $values);

            if (true === $automaticValueFound) {
                continue;
            }

            if (BowlInterface::METHOD_NAME === $name) {
                $values[] = $this->name;
                continue;
            }

            if (!$parameter->isOptional()) {
                throw new RuntimeException("Missing the parameter {$parameter->getName()} in the WorkPlan");
            }

            $values[] = $parameter->getDefaultValue();
        }

        return $values;
    }
}
