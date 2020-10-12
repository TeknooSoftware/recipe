<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Bowl;

use Teknoo\Recipe\ChefInterface;

/**
 * Default base implementation for Bowl to manage parameter mapping with the workplan's ingredients.
 *
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
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
     * @var array<string, \ReflectionParameter>
     */
    private ?array $parametersCache = null;

    /**
     * To return the Reflection instance about this callable, supports functions, closures, objects methods or class
     * methods.
     *
     * @param callable $callable
     *
     * @return \ReflectionFunctionAbstract
     * @throws \ReflectionException
     */
    private function getReflection(callable $callable): \ReflectionFunctionAbstract
    {
        if (\is_array($callable)) {
            //The callable is checked by PHP in the constructor by the type hiting
            $reflectionClass = new \ReflectionClass($callable[0]);

            return $reflectionClass->getMethod($callable[1]);
        }

        if (\is_string($callable) || $callable instanceof \Closure) {
            return new \ReflectionFunction($callable);
        }

        //It's not a closure, so it's mandatory a invokable object (because the callable is valid)
        $reflectionClass = new \ReflectionClass($callable);

        return $reflectionClass->getMethod('__invoke');
    }

    /**
     * To extract the list of ReflectionParameter instances about the current callable.
     *
     * @param callable $callable
     *
     * @return array<string, \ReflectionParameter>
     * @throws \ReflectionException
     */
    private function listParameters(callable $callable): array
    {
        $parameters = [];
        foreach ($this->getReflection($callable)->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }

        return $parameters;
    }

    /**
     * To extract the list of ReflectionParameter instances about the current callable
     * and cache them for next call.
     *
     * @param callable $callable
     *
     * @return array<string, \ReflectionParameter>
     * @throws \ReflectionException
     */
    private function getParametersInOrder(callable $callable): array
    {
        if (null === $this->parametersCache) {
            $this->parametersCache = $this->listParameters($callable);
        }

        return $this->parametersCache;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param array<string, mixed> $workPlan
     * @param array<mixed> $values
     * @return bool
     */
    private function findInstanceForParameter(\ReflectionParameter $parameter, array &$workPlan, array &$values): bool
    {
        $automaticValueFound = false;

        foreach ($workPlan as &$variable) {
            if (\is_object($variable) && $this->isInstanceOf($parameter, $variable)) {
                $values[] = $variable;
                $automaticValueFound = true;
                break;
            }
        }

        return $automaticValueFound;
    }

    /**
     * @param object $instance
     */
    private function isInstanceOf(\ReflectionParameter $parameter, object $instance): bool
    {
        $type = $parameter->getType();
        if (!$type instanceof \ReflectionUnionType && !$type instanceof \ReflectionNamedType) {
            return false;
        }

        $checkType = static function (\ReflectionNamedType $type) use ($parameter, $instance): bool {
            if ($type->isBuiltin()) {
                return false;
            }

            $className = $type->getName();
            if ('self' === $className) {
                return $parameter->getDeclaringClass()->isInstance($instance);
            }

            $rfClass = new \ReflectionClass($className);
            return $rfClass->isInstance($instance);
        };

        if ($type instanceof \ReflectionUnionType) {
            foreach ($type->getTypes() as $subType) {
                if ($subType instanceof \ReflectionNamedType && $checkType($subType)) {
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
     * @param callable $callable
     * @param ChefInterface $chef
     * @param array<string, mixed> $workPlan
     *
     * @return array<mixed>
     *
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function extractParameters(callable $callable, ChefInterface $chef, array &$workPlan): array
    {
        $values = [];
        foreach ($this->getParametersInOrder($callable) as $name => $parameter) {
            if ($this->isInstanceOf($parameter, $chef)) {
                $values[] = $chef;
                continue;
            }

            if (!empty($this->mapping[$name])) {
                $mapping = $this->mapping[$name];
                if (\is_string($mapping)) {
                    $name = $mapping;
                } elseif (\is_array($mapping)) {
                    \reset($mapping);
                    do {
                        $name = \current($mapping);
                    } while (!isset($workPlan[$name]) && \next($mapping));
                }
            }

            if (isset($workPlan[$name])) {
                $values[] = $workPlan[$name];
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
                throw new \RuntimeException("Missing the parameter {$parameter->getName()} in the WorkPlan");
            }

            $values[] = $parameter->getDefaultValue();
        }

        return $values;
    }
}
