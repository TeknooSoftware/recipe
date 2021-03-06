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

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;

/**
 * Bowl to execute a callable available into the workplan an not instantiate with a callable
 *
 * @see BowlInterface
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
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

    private string $callableKeyName;

    private bool $throwIfNotExisting;

    /**
     * @var null|callable
     */
    private $previousCallable;

    /**
     * DynamicBowl constructor.
     * @param array<string, string|string[]> $mapping
     */
    public function __construct(
        string $callableKeyName,
        bool $throwIfNotExisting,
        $mapping = [],
        string $name = ''
    ) {
        $this->uniqueConstructorCheck();

        $this->callableKeyName = $callableKeyName;
        $this->throwIfNotExisting = $throwIfNotExisting;
        $this->mapping = $mapping;
        $this->name = $name;
    }

    /**
     * Extract the callable from the workplan, null if it has not been found. If the element in the workPlan is not
     * a callable (but exist), this method throw an exception.
     *
     * @param array<string, mixed> $workPlan
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

    private function checkToClearsParametersCache(callable $callable): void
    {
        if ($this->previousCallable !== $callable) {
            $this->parametersCache = null;
        }

        $this->previousCallable = $callable;
    }

    /**
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
