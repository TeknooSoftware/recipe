<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Bowl;

use BadMethodCallException;
use Closure;
use Exception;
use Fiber;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionUnionType;
use RuntimeException;
use Teknoo\Recipe\Bowl\Exception\SelfParameterNotInstantiableException;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Recipe\Ingredient\TransformableInterface;

use function class_exists;
use function current;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function next;
use function reset;
use function sprintf;

/**
 * Default base implementation for Bowl to manage parameter mapping with the workplan's ingredients.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait BowlTrait
{
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
        $getter = static fn(): ReflectionClass =>
            self::$reflectionsClasses[$objectOrClass] = new ReflectionClass($objectOrClass);

        return self::$reflectionsClasses[$objectOrClass] ?? $getter();
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
            $reflectionClass = self::getReflectionClass($objectOrClass);

            return self::$reflectionsMethods[$objectOrClass][$methodName] = $reflectionClass->getMethod($methodName);
        };

        return self::$reflectionsMethods[$objectOrClass][$methodName] ?? $getter();
    }

    private static function getReflectionFunction(string $function): ReflectionFunction
    {
        $getter = static fn(): ReflectionFunction =>
            self::$reflectionsFunctions[$function] = new ReflectionFunction($function);

        return self::$reflectionsFunctions[$function] ?? $getter();
    }

    private static function getReflectionInvokable(object $invokable): ReflectionMethod
    {
        $invokableClass = $invokable::class;

        $getter = static function () use ($invokableClass): ReflectionMethod {
            $reflectionClass = new ReflectionClass($invokableClass);

            return self::$reflectionsInvokables[$invokableClass] = $reflectionClass->getMethod('__invoke');
        };

        return self::$reflectionsInvokables[$invokableClass] ?? $getter();
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
            return self::getReflectionMethod($callable[0], $callable[1]);
        }

        if ($callable instanceof Closure) {
            return new ReflectionFunction($callable);
        }

        if (is_string($callable)) {
            return self::getReflectionFunction($callable);
        }

        //It's not a closure, so it's mandatory a invokable object (because the callable is valid)
        return self::getReflectionInvokable((object) $callable);
    }

    /**
     * To extract the list of ReflectionParameter instances about the current callable.
     *
     * @return array<string, ReflectionParameter>|ReflectionParameter[]
     * @throws ReflectionException
     */
    private function listParameters(callable $callable): array
    {
        $reflection = self::getReflection($callable);
        $oid = $reflection->getFileName() . ':' . $reflection->getStartLine();

        $getter = static function () use ($oid, $reflection): array {
            $parameters = [];
            foreach ($reflection->getParameters() as $parameter) {
                $parameters[$parameter->getName()] = $parameter;
            }

            return self::$reflectionsParameters[$oid] = $parameters;
        };

        return self::$reflectionsParameters[$oid] ?? $getter();
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
        ?string $transformClassName,
    ): bool {
        $automaticValueFound = false;

        $refClass = null;
        if ($allowTransform && null !== $transformClassName && class_exists($transformClassName)) {
            $refClass = self::getReflectionClass($transformClassName);
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

            /** @var class-string|'self' $className */
            $className = $type->getName();
            if ('self' === $className) {
                if (null === ($declaringClass = $parameter->getDeclaringClass())) {
                    $function = $parameter->getDeclaringFunction();
                    throw new SelfParameterNotInstantiableException(
                        sprintf(
                            "Can not fetch declaring class from 'self' for parameter in this bowl for %s in %s:%s",
                            $function->getName(),
                            $function->getFileName(),
                            $function->getStartLine(),
                        )
                    );
                }

                return $declaringClass->isInstance($instance);
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
     * @param array<string, mixed> $values
     * @param Fiber<mixed, mixed, mixed, mixed> $fiber
     */
    protected function findReservedParameterInstance(
        ReflectionParameter $parameter,
        array &$values,
        ChefInterface $chef,
        ?Fiber $fiber,
        ?CookingSupervisorInterface $cookingSupervisor,
    ): bool {
        if ($this->isInstanceOf($parameter, $chef)) {
            $values[] = $chef;
            return true;
        }

        if (null !== $fiber && $this->isInstanceOf($parameter, $fiber)) {
            $values[] = $fiber;
            return true;
        }

        if (null !== $cookingSupervisor && $this->isInstanceOf($parameter, $cookingSupervisor)) {
            $values[] = $cookingSupervisor;
            return true;
        }

        return false;
    }

    /**
     * @param array<string, mixed> $workPlan
     * @param array<string, mixed> $values
     */
    public function findParameterValueFromWorkplan(
        ReflectionParameter $parameter,
        string $name,
        array &$values,
        array &$workPlan,
        bool &$skip,
        bool $allowTransform,
        ?string $transformClassName,
    ): mixed {
        return match (true) {
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
                $transformClassName,
            ) => $skip = true,

            //Special name `_methodName`
            BowlInterface::METHOD_NAME === $name => $this->name,

            //Not found, if it is not optional, throw an exception
            !$parameter->isOptional() => throw new BadMethodCallException(
                sprintf(
                    "Missing the parameter %s (%s) in the WorkPlan for %s::%s in %s:%s",
                    $parameter->getName(),
                    $name,
                    $parameter->getDeclaringClass()?->getName(),
                    $parameter->getDeclaringFunction()->getName(),
                    $parameter->getDeclaringFunction()->getFileName(),
                    $parameter->getDeclaringFunction()->getStartLine(),
                )
            ),

            //Return the default value
            default => $parameter->getDefaultValue(),
        };
    }

    /**
     * To map each callable's arguments to ingredients available into the workplan.
     *
     * @param array<string, mixed> $workPlan
     * @param Fiber<mixed, mixed, mixed, mixed> $fiber
     *
     * @return array<mixed>
     *
     * @throws Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function extractParameters(
        callable $callable,
        ChefInterface $chef,
        array &$workPlan,
        ?Fiber $fiber,
        ?CookingSupervisorInterface $cookingSupervisor,
    ): array {
        $values = [];
        foreach ($this->listParameters($callable) as $name => $parameter) {
            if ($this->findReservedParameterInstance($parameter, $values, $chef, $fiber, $cookingSupervisor)) {
                continue;
            }

            //Check if we must transform an ingredient before put it into the bowl
            $allowTransform = false;
            $transformClassName = null;
            $transformer = null;
            if (!empty($attributes = $parameter->getAttributes(Transform::class))) {
                /** @var Transform $attr */
                $attr = $attributes[0]->newInstance();
                $transformClassName = $attr->getClassName();
                $transformer = $attr->getTransformer();
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
            $tempValue = $this->findParameterValueFromWorkplan(
                $parameter,
                $name,
                $values,
                $workPlan,
                $skip,
                $allowTransform,
                $transformClassName,
            );

            if (true === $skip) {
                continue;
            }

            if ($allowTransform) {
                if (is_callable($transformer)) {
                    $tempValue = $transformer($tempValue);
                }

                if ($tempValue instanceof TransformableInterface) {
                    $tempValue = $tempValue->transform();
                }
            }

            $values[] = $tempValue;
        }

        return $values;
    }
}
