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
 * Bowl to execute a callable available into the workplan an not instantiate with a callable
 *
 * @see BowlInterface
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class DynamicBowl implements BowlInterface
{
    use ImmutableTrait;
    use BowlTrait;

    /**
     * @var string
     */
    private $callableKeyName;

    /**
     * @var bool
     */
    private $throwIfNotExisting;

    /**
     * @var null|callable
     */
    private $previousCallable = null;

    /**
     * DynamicBowl constructor.
     * @param string $callableKeyName
     * @param bool $throwIfNotExisting
     * @param array $mapping
     */
    public function __construct(string $callableKeyName, bool $throwIfNotExisting, array $mapping = [])
    {
        $this->uniqueConstructorCheck();

        $this->callableKeyName = $callableKeyName;
        $this->throwIfNotExisting = $throwIfNotExisting;
        $this->mapping = $mapping;
    }

    /**
     * Extract the callable from the workplan, null if it has not been found. If the element in the workPlan is not
     * a callable (but exist), this method throw an exception.
     *
     * @param array $workPlan
     * @return callable|null
     */
    private function getCallable(array &$workPlan): ?callable
    {
        if (!isset($workPlan[$this->callableKeyName])) {
            return null;
        }

        $callable = $workPlan[$this->callableKeyName];

        if (!\is_callable($callable)) {
            throw new \RuntimeException(
                \sprintf(
                    'Error, the element identified by %s in the work plan is not a callable',
                    $this->callableKeyName
                )
            );
        }

        return $callable;
    }

    /**
     * @param callable $callable
     */
    private function checkToClearsParametersCache(callable $callable)
    {
        if ($this->previousCallable !== $callable) {
            $this->parametersCache = null;
        }

        $this->previousCallable = $callable;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(ChefInterface $chef, array &$workPlan): BowlInterface
    {
        $callable = $this->getCallable($workPlan);

        if (null === $callable && true === $this->throwIfNotExisting) {
            throw new \RuntimeException(
                \sprintf('Error, there are no callable in the work plan at %s', $this->callableKeyName)
            );
        }

        if (null === $callable) {
            return $this;
        }

        $this->checkToClearsParametersCache($callable);
        $values = $this->extractParameters($callable, $chef, $workPlan);

        $callable(...$values);

        return $this;
    }
}
