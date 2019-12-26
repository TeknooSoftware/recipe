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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Recipe;

use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * @see Recipe
 *
 * Default state of a recipe, able to complete them with required ingredients and steps of the recipe.
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
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

    public function addStep(): callable
    {
        return function ($action, string $name, array $with = [], int $position = null): RecipeInterface {
            if (!$action instanceof BowlInterface && !\is_callable($action)) {
                throw new \TypeError('$action accepts only callable value or a BowlInterface instance');
            }

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

    public function setOnError(): callable
    {
        return function ($action): RecipeInterface {
            /**
             * @var Recipe $this
             */
            $that = $this->cloneMe();

            $callable = $action;
            if (!$action instanceof BowlInterface) {
                $callable = new Bowl($action, []);
            }

            $that->onError[] = $callable;

            return $that;
        };
    }

    public function addSubRecipe(): callable
    {
        return function (RecipeInterface $recipe, string $name, $repeat = 1, int $position = null): RecipeInterface {
            /**
             * @var Recipe $this
             */
            $that = $this->cloneMe();

            if (\is_callable($repeat)) {
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
