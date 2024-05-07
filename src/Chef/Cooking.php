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

namespace Teknoo\Recipe\Chef;

use SensitiveParameter;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;
use Throwable;

use function array_keys;
use function count;

/**
 * @see Chef
 *
 * State representing a chef instance cooking a recipe. It is enable only if a chef is trained and flaggad as cooking.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @mixin Chef
 */
class Cooking implements StateInterface
{
    use StateTrait;

    /*
     * To start a sub recipe with a subchef, cloned from the current chef with a copy of its workplan.
     * Two worplans will evolve independently
     */
    private function begin(): callable
    {
        return function (
            BaseRecipeInterface $recipe,
            ?CookingSupervisorInterface $supervisor,
        ): ChefInterface {
            $chef = new static(null, $this, $supervisor ?? $this->cookingSupervisor);

            if ($recipe instanceof RecipeInterface) {
                $chef->read($recipe);
                $chef->workPlan = $this->workPlan;

                return $chef;
            }

            $recipe->train($chef);
            $chef->workPlan += $this->workPlan;

            return $chef;
        };
    }

    /*
     * To memorize a missing ingredients to stop the cooking of the recipe.
     */
    private function missingIngredient(): callable
    {
        return function (IngredientInterface $ingredient, string $message): ChefInterface {
            $this->missingIngredients[$message] = $ingredient;

            return $this;
        };
    }

    /*
     * To get the next step in the cooking to execute
     */
    private function getNextStep(): callable
    {
        return function (string $nextStep = null): ?BowlInterface {
            if (!empty($nextStep) && isset($this->stepsNames[$nextStep])) {
                $this->position = $this->stepsNames[$nextStep];
            }

            if (count($this->steps) > $this->position) {
                $position = $this->position;

                $step = $this->steps[$position];
                ++$this->position;

                return $step;
            }

            return null;
        };
    }

    /**
     * Called by a step to continue the execution of the recipe but before, update ingredients available on the workplan
     * @param array<string, mixed> $with
     */
    private function continueRecipe(): callable
    {
        return function (array $with = [], string $nextStep = null): ChefInterface {
            /**
             * @var Chef $this
             */
            $this->updateMyWorkPlan($with);

            while (true === $this->cooking && ($callable = $this->getNextStep($nextStep)) instanceof BowlInterface) {
                try {
                    $callable->execute($this, $this->workPlan, $this->cookingSupervisor);
                } catch (Throwable $error) {
                    $this->callErrors($error);
                }

                $nextStep = null;
            }

            return $this;
        };
    }

    /**
     * Called by a step to stop the execution of the recipe and check if the dish is the result excepted.
     * @param mixed $result
     */
    private function finishRecipe(): callable
    {
        return function ($result): ChefInterface {
            $this->interruptCooking();

            /**
             * @var Chef $this
             */
            //This method is called only if $this->recipe is a valid RecipeInterface instance
            $this->recipe->validate($result);

            return $this;
        };
    }

    /*
     * Called by a step to stop the execution because an error was occured
     */
    private function errorInRecipe(): callable
    {
        return function (#[SensitiveParameter] Throwable $error): ChefInterface {
            $this->callErrors($error);

            return $this;
        };
    }

    /*
     * Internal method to prepare a cooking, check is all ingredients are available.
     * @internal
     */
    private function prepare(): callable
    {
        return function (): void {
            /**
             * @var Chef $this
             */
            $this->recipe->prepare($this->workPlan, $this);

            $this->checkMissingIngredients();

            $this->position = 0;
        };
    }

    /*
     * To check if the chef has memorized some missing ingredients
     */
    private function checkMissingIngredients(): callable
    {
        return function (): void {
            /**
             * @var Chef $this
             */
            if (empty($this->missingIngredients)) {
                return;
            }

            throw new Chef\Exception\MissingIngredientException(
                'Error, missing some ingredients : '
                . implode(', ', array_keys($this->missingIngredients))
            );
        };
    }
}
