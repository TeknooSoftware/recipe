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

namespace Teknoo\Recipe\Recipe;

use Teknoo\Recipe\Bowl\Bowl;
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
 */
class Draft implements StateInterface
{
    use StateTrait;

    public function addIngredient()
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

    public function addStep()
    {
        return function (callable $action, string $name, array $with = [], int $position = null): RecipeInterface {
            /**
             * @var Recipe $this
             */
            $that = $this->cloneMe();

            $callable = new Bowl($action, $with);

            if (empty($position)) {
                $that->steps[] = [[$name => $callable]];
            } else {
                $that->steps[$position][] = [$name => $callable];
            }

            return $that;
        };
    }

    public function setExceptedDish()
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
