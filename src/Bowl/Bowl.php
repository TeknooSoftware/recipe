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

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;

class Bowl implements BowlInterface
{
    use ImmutableTrait;

    /**
     * @var callable
     */
    private $callable;

    /**
     * @var array
     */
    private $mapping;

    /**
     * @var string[]
     */
    private $parametersCache = null;

    /**
     * Bowl constructor.
     * @param callable $callable
     * @param array $mapping
     */
    public function __construct(callable $callable , array $mapping)
    {
        $this->uniqueConstructorCheck();

        $this->callable = $callable;
        $this->mapping = $mapping;
    }

    /**
     * @return \ReflectionFunctionAbstract
     */
    private function getReflection(): \ReflectionFunctionAbstract
    {
        if (\is_array($this->callable)) {
            if (!\is_object($this->callable[0]) && !\class_exists($this->callable[0])) {
                throw new \RuntimeException("Error, the class {$this->callable[0]} does not exist");
            }

            $reflectionClass = new \ReflectionClass($this->callable[0]);

            if (!$reflectionClass->hasMethod($this->callable[1])) {
                throw new \RuntimeException("Error, the method {$this->callable[1]} is not available");
            }

            return $reflectionClass->getMethod($this->callable[1]);
        }

        return new \ReflectionFunction($this->callable);
    }

    /**
     * @return \ReflectionParameter[]
     */
    private function listParameters(): array
    {
        $parameters = [];
        foreach ($this->getReflection()->getParameters() as $parameter) {
            $parameters[$parameter->getName()] = $parameter;
        }

        return $parameters;
    }

    /**
     * @return \ReflectionParameter[]
     */
    private function getParametersInOrder(): array
    {
        if (null === $this->parametersCache) {
            $this->parametersCache = $this->listParameters();
        }

        return $this->parametersCache;
    }

    /**
     * @param ChefInterface $chef
     * @param array $workPlan
     * @return array
     * @throws \Exception
     */
    private function extractParameters(ChefInterface $chef, array $workPlan): array
    {
        $values = [];
        foreach ($this->getParametersInOrder() as $name=>$parameter) {
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

            if (!$class instanceof \ReflectionClass) {
                if (!$parameter->isOptional()) {
                    throw new \Exception("Missing the parameter {$parameter->getName()} in the WorkPlan");
                }

                continue;
            }

            foreach ($workPlan as $variable) {
                if ($class->isInstance($variable)) {
                    $values[] = $variable;
                }
            }
        }

        return $values;
    }

    /**
     * @inheritDoc
     */
    public function execute(ChefInterface $chef, array $workPlan): BowlInterface
    {
        $values = $this->extractParameters($chef, $workPlan);

        ($this->callable)(...$values);

        return $this;
    }
}