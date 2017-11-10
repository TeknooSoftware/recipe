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

namespace Teknoo\Recipe;

use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface RecipeInterface extends ImmutableInterface
{
    /**
     * @param IngredientInterface $ingredient
     * @return RecipeInterface
     */
    public function require(IngredientInterface $ingredient): RecipeInterface;

    /**
     * @param callable $action
     * @param array $with
     * @param int|null $position
     * @return RecipeInterface
     */
    public function do(callable $action, array $with=[], int $position=null): RecipeInterface;

    /**
     * @param DishInterface $dish
     * @return RecipeInterface
     */
    public function given(DishInterface $dish): RecipeInterface;

    /**
     * @param ChefInterface $chef
     * @return RecipeInterface
     */
    public function train(ChefInterface $chef): RecipeInterface;

    /**
     * @param array $workPlan
     * @param ChefInterface $chef
     * @return RecipeInterface
     */
    public function prepare(array &$workPlan, ChefInterface $chef): RecipeInterface;

    /**
     * @param $value
     * @return RecipeInterface
     */
    public function validate($value): RecipeInterface;
}
