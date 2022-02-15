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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Bowl;

use Exception;
use RuntimeException;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;

use function is_callable;
use function sprintf;

/**
 * Abstract bowl class to execute a callable available into the workplan but not available directly at the
 * recipe writing
 *
 * @see BowlInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractDynamicBowl implements BowlInterface
{
    use ImmutableTrait;
    use BowlTrait;

    /**
     * @var null|callable
     */
    private $previousCallable;

    /**
     * DynamicBowl constructor.
     * @param array<string, string|string[]> $mapping
     */
    public function __construct(
        private readonly string $callableKeyName,
        private readonly bool $throwIfNotExisting,
        private readonly array $mapping = [],
        private readonly string $name = ''
    ) {
        $this->uniqueConstructorCheck();
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

        if (!is_callable($callable)) {
            throw new RuntimeException(
                sprintf(
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
     * @param array<string, mixed> $workPlan
     * @throws RuntimeException if a required argument can not be mapped.
     */
    abstract protected function processToExecution(
        callable $callable,
        ChefInterface $chef,
        array &$workPlan
    ): void;

    /**
     * @throws Exception
     */
    public function execute(ChefInterface $chef, array &$workPlan): BowlInterface
    {
        $callable = $this->getCallable($workPlan);

        if (null === $callable && true === $this->throwIfNotExisting) {
            throw new RuntimeException(
                sprintf('Error, there are no callable in the work plan at %s', $this->callableKeyName)
            );
        }

        if (null === $callable) {
            return $this;
        }

        $this->checkToClearsParametersCache($callable);

        $this->processToExecution($callable, $chef, $workPlan);

        return $this;
    }
}
