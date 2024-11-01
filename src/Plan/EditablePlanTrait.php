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

namespace Teknoo\Recipe\Plan;

use Closure;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\RecipeInterface;
use Traversable;

/**
 * Trait to create natively an editable plan, allow developers to extends easily a plan without implement this
 * behavior in theirs plans classes. A plan can be directly extended in a container service definition.
 * More step or error handler can be added to an existing plan.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait EditablePlanTrait
{
    use BasePlanTrait {
        doPopulatingOfRecipe as private doPopulatinOfRecipeTrait;
    }

    /**
     * @var array<int, array<int,BowlInterface|Step|callable>>
     */
    private array $steps = [];

    /**
     * @var array<callable>
     */
    private array $additionalErrorHandlers = [];

    public function add(BowlInterface|Step|callable $action, int $position): EditablePlanInterface
    {
        $this->steps[$position][] = $action;

        return $this;
    }

    public function addErrorHandler(callable $handler): EditablePlanInterface
    {
        $this->additionalErrorHandlers[] = $handler;

        return $this;
    }

    /**
     * @return Traversable<BowlInterface|Step|callable>
     */
    private function listSteps(): Traversable
    {
        $stepsList = $this->steps;
        ksort($stepsList);
        foreach ($stepsList as $priority => &$stepSubLists) {
            foreach ($stepSubLists as &$step) {
                yield $priority => $step;
            }
        }
    }

    /**
     * @param iterable<int, callable> $steps
     */
    private function registerAdditionalSteps(RecipeInterface $recipe): RecipeInterface
    {
        $counter = 0;
        foreach ($this->listSteps() as $position => $step) {
            $with = [];
            if ($step instanceof Step) {
                $with = $step->getWith();
                $step = $step->getStep();
            }

            $class = 'AdditionalStep' . $counter++;
            if (is_object($step) && !$step instanceof Closure) {
                $class = $step::class;
            } elseif (is_array($step) && is_object($step[0])) {
                $class = $step[0]::class;
            }

            $recipe = $recipe->cook($step, $class, $with, (int) $position);
        }

        return $recipe;
    }

    /**
     * @param iterable<callable> $handlers
     */
    private function registerAdditionalErrorHandler(RecipeInterface $recipe): RecipeInterface
    {
        foreach ($this->additionalErrorHandlers as $handler) {
            $recipe = $recipe->onError(new Bowl($handler, ['result' => 'exception']));
        }

        return $recipe;
    }

    private function doPopulatingOfRecipe(RecipeInterface $recipe): RecipeInterface
    {
        $recipe = $this->populateRecipe($recipe);

        $recipe = $this->registerAdditionalSteps($recipe);

        $recipe = $this->registerAdditionalErrorHandler($recipe);

        return $recipe;
    }
}
