<?php

declare(strict_types=1);

/**
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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Recipe\Bowl;

use Teknoo\Recipe\ChefInterface;

/**
 * Default base implementation for Bowl to manage parameter mapping with the workplan's ingredients.
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait BowlTrait
{
    /**
     * To map some argument's name to another ingredient name on the workplan.
     *
     * @var array
     */
    private $mapping = [];

    /**
     * To cache the reflections about parameters of the callable
     *
     * @var string[]
     */
    private $parametersCache = null;

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

        if (\is_object($callable) && !$callable instanceof \Closure) {
            //It's not a closure, so it's mandatory a invokable object (because the callable is valid)
            $reflectionClass = new \ReflectionClass($callable);

            return $reflectionClass->getMethod('__invoke');
        }

        return new \ReflectionFunction($callable);
    }

    /**
     * To extract the list of ReflectionParameter instances about the current callable.
     *
     * @param callable $callable
     *
     * @return \ReflectionParameter[]
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
     * @return \ReflectionParameter[]
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
     * @param \ReflectionClass $class
     * @param array $workPlan
     * @param array $values
     * @return bool
     */
    private function findInstanceForParameter(\ReflectionClass $class, array &$workPlan, array &$values)
    {
        $automaticValueFound = false;

        foreach ($workPlan as &$variable) {
            if (\is_object($variable) && $class->isInstance($variable)) {
                $values[] = $variable;
                $automaticValueFound = true;
                break;
            }
        }

        return $automaticValueFound;
    }

    /**
     * To map each callable's arguments to ingredients available into the workplan.
     *
     * @param callable $callable
     * @param ChefInterface $chef
     * @param array $workPlan
     *
     * @return array
     *
     * @throws \Exception
     */
    private function extractParameters(callable $callable, ChefInterface $chef, array &$workPlan): array
    {
        $values = [];
        foreach ($this->getParametersInOrder($callable) as $name => $parameter) {
            $class = $parameter->getClass();

            if ($class instanceof \ReflectionClass && $class->isInstance($chef)) {
                $values[] = $chef;
                continue;
            }

            if (!empty(($this->mapping[$name]))) {
                $name = $this->mapping[$name];
            }

            if (isset($workPlan[$name])) {
                $values[] = $workPlan[$name];
                continue;
            }

            if ($class instanceof \ReflectionClass) {
                $automaticValueFound = $this->findInstanceForParameter($class, $workPlan, $values);

                if (true === $automaticValueFound) {
                    continue;
                }
            }

            if (!$parameter->isOptional()) {
                throw new \RuntimeException("Missing the parameter {$parameter->getName()} in the WorkPlan");
            }
        }

        return $values;
    }
}
