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
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Bowl;

use Exception;
use RuntimeException;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Bowl\Exception\MissingCallableIngredientException;
use Teknoo\Recipe\Bowl\Exception\MissingIngredientException;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\Value;

use function is_callable;
use function sprintf;

/**
 * Abstract bowl class to execute a callable available into the workplan but not available directly at the
 * recipe writing
 *
 * @see BowlInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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
     * @param array<string, string|string[]|Value> $mapping
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
            throw new MissingCallableIngredientException(
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
        callable &$callable,
        ChefInterface $chef,
        array &$workPlan,
        ?CookingSupervisorInterface $cookingSupervisor,
    ): void;

    /**
     * @throws Exception
     */
    public function execute(
        ChefInterface $chef,
        array &$workPlan,
        ?CookingSupervisorInterface $cookingSupervisor = null,
    ): BowlInterface {
        $callable = $this->getCallable($workPlan);

        if (null === $callable && true === $this->throwIfNotExisting) {
            throw new MissingIngredientException(
                sprintf('Error, there are no callable in the work plan at %s', $this->callableKeyName)
            );
        }

        if (null === $callable) {
            return $this;
        }

        $this->checkToClearsParametersCache($callable);

        $this->processToExecution($callable, $chef, $workPlan, $cookingSupervisor);

        return $this;
    }
}
