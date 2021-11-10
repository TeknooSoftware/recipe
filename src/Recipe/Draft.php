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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Recipe;

use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

use function is_callable;

/**
 * @see Recipe
 *
 * Default state of a recipe, able to complete them with required ingredients and steps of the recipe.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @mixin Recipe
 */
class Draft implements StateInterface
{
    use StateTrait;

    /*
     * To define a new required ingredient to execute the recipe, a new recipe object will be returned
     */
    public function addIngredient(): callable
    {
        return function (IngredientInterface $ingredient): RecipeInterface {
            /**
             * @var Recipe $this
             */
            $that = $this->cloneMe();
            $that->requiredIngredients[] = $ingredient;

            return $that;
        };
    }

    /*
     * To push a step in the recipe, a new recipe object will be returned. If the step is a callable, it will be wrapped
     * into a Bowl object, else, the BowlInterface instance will be directly used
     */
    public function addStep(): callable
    {
        return function (
            callable | BowlInterface $action,
            string $name,
            array $with = [],
            int $position = null
        ): RecipeInterface {
            /**
             * @var Recipe $this
             */
            $that = $this->cloneMe();

            $callable = $action;
            if (!$callable instanceof BowlInterface) {
                $callable = new Bowl($callable, $with, $name);
            }

            if (empty($position)) {
                $that->steps[] = [[$name => $callable]];
            } else {
                $that->steps[$position][] = [$name => $callable];
            }

            return $that;
        };
    }

    /*
     * To define an error handler about this recipe, a new recipe object will be returned
     */
    public function setOnError(): callable
    {
        return function (callable | BowlInterface $callable): RecipeInterface {
            /**
             * @var Recipe $this
             */
            $that = $this->cloneMe();

            if (!$callable instanceof BowlInterface) {
                $callable = new Bowl($callable, []);
            }

            $that->onError[] = $callable;

            return $that;
        };
    }

    /*
     * To define / add a sub recipe into this recipe recipe. It will be wrapped into a RecipeBowl instance. The repeat
     * condition can be a callable and will be wrapped into a bowl in this case.
     * A new recipe object will be returned
     */
    public function addSubRecipe(): callable
    {
        return function (
            BaseRecipeInterface $recipe,
            string $name,
            int | callable $repeat = 1,
            int $position = null
        ): RecipeInterface {
            /**
             * @var Recipe $this
             */
            $that = $this->cloneMe();

            if (is_callable($repeat)) {
                $repeat = new Bowl($repeat, []);
            }

            $callable = new RecipeBowl($recipe, $repeat);

            if (empty($position)) {
                $that->steps[] = [[$name => $callable]];
            } else {
                $that->steps[$position][] = [$name => $callable];
            }

            return $that;
        };
    }

    /*
     * To define excepted dish resulting of the recipe, a new recipe object will be returned
     */
    public function setExceptedDish(): callable
    {
        return function (DishInterface $dish): RecipeInterface {
            /**
             * @var Recipe $this
             */
            $that = $this->cloneMe();
            $that->exceptedDish = $dish;

            return $that;
        };
    }
}
