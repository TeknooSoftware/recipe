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

namespace Teknoo\Recipe\Chef;

use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * @see Chef
 *
 * State representing a chef instance cooking a recipe. It is enable only if a chef is trained and flaggad as cooking.
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Cooking implements StateInterface
{
    use StateTrait;

    public function begin()
    {
        return function (RecipeInterface $recipe): ChefInterface {
            $chef = new static($recipe);
            $chef->workPlan = $this->workPlan;

            return $chef;
        };
    }

    /**
     * To memorize a missing ingredients to stop the cooking of the recipe.
     */
    public function missingIngredient()
    {
        return function (IngredientInterface $ingredient, string $message): ChefInterface {
            /**
             * @var Chef $this
             */
            $this->missingIngredients[$message] = $ingredient;

            return $this;
        };
    }

    /**
     * To get the next step in the cooking to execute
     */
    private function getNextStep()
    {
        return function (string $nextStep = null): ?BowlInterface {
            /**
             * @var Chef $this
             */

            if (!empty($nextStep) && isset($this->stepsNames[$nextStep])) {
                $this->position = $this->stepsNames[$nextStep];
            }

            if (count($this->steps) > $this->position) {
                $position = $this->position;

                $step = $this->steps[$position];
                $this->position++;

                return $step;
            }

            return null;
        };
    }

    /**
     * Called by a step to continue the execution of the recipe but before, update ingredients available on the workplan
     */
    public function continueRecipe()
    {
        return function (array $with = [], string $nextStep = null): ChefInterface {
            /**
             * @var Chef $this
             */
            $this->updateMyWorkPlan($with);

            while (($callable = $this->getNextStep($nextStep)) instanceof BowlInterface) {
                try {
                    $callable->execute($this, $this->workPlan);
                } catch (\Throwable $error) {
                    if ($this->onError instanceof BowlInterface) {
                        $this->workPlan['exception'] = $error;
                        $this->onError->execute($this, $this->workPlan);
                    }

                    throw $error;
                }

                $nextStep = null;
            }

            return $this;
        };
    }

    /**
     * Called by a step to stop the execution of the recipe and check if the dish is the result excepted.
     */
    public function finishRecipe()
    {
        return function ($result): ChefInterface {
            /**
             * @var Chef $this
             */
            //This method is called only if $this->recipe is a valid RecipeInterface instance
            $this->recipe->validate($result);

            $this->position = \count($this->steps) + 1;

            return $this;
        };
    }

    /**
     * Internal method to prepare a cooking, check is all ingredients are available.
     * @internal
     */
    private function prepare()
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

    /**
     * Internal method to clean the workplan after cooking.
     * @internal
     */
    private function clean()
    {
        return function (): void {
            /**
             * @var Chef $this
             */
            $this->workPlan = [];
            $this->cooking = false;

            $this->updateStates();
        };
    }

    /**
     * To check if the chef has memorized some missing ingredients
     */
    private function checkMissingIngredients()
    {
        return function (): void {
            /**
             * @var Chef $this
             */
            if (empty($this->missingIngredients)) {
                return;
            }

            throw new \RuntimeException(
                'Error, missing some ingredients : '
                . implode(', ', \array_keys($this->missingIngredients))
            );
        };
    }
}
