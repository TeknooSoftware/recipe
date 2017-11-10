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

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
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
            //The callable is checked by PHP in the constructor by the type hiting
            $reflectionClass = new \ReflectionClass($this->callable[0]);

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

            if ($class instanceof \ReflectionClass) {
                $automaticValueFound = false;

                foreach ($workPlan as &$variable) {
                    if (\is_object($variable) && $class->isInstance($variable)) {
                        $values[] = $variable;
                        $automaticValueFound = true;
                        break;
                    }
                }

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