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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Recipe;

use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;

/**
 * Interface to define a recipe. A recipe has several ordered steps (as callable). It can have several required
 * ingredients needed to start the cooking and the excepted dish attempted.
 *
 * A recipe instance must be immutable. Each call to this method must be performed on a clone and not update the state
 * of the recipe.
 * When a chef learn, the recipe returned must be frozen and not accept any new step or ingredient.
 *
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
     * To define required ingredients to start the cooking of the recipe.
     *
     * @param IngredientInterface $ingredient
     * @return RecipeInterface
     */
    public function require(IngredientInterface $ingredient): RecipeInterface;

    /**
     * To define actions to realize the recipe.
     *
     * @param callable|BowlInterface $action
     * @param string $name
     * @param array $with
     * @param int|null $position
     * @return RecipeInterface
     */
    public function cook($action, string $name, array $with = [], int $position = null): RecipeInterface;

    /**
     * To define action when an error is occurred
     *
     * @param callable|BowlInterface $action
     * @return RecipeInterface
     */
    public function onError($action): RecipeInterface;

    /**
     * To define actions to realize the recipe.
     *
     * @param RecipeInterface $recipe
     * @param string $name
     * @param int|callable $repeat
     * @param int|null $position
     * @return RecipeInterface
     */
    public function execute(RecipeInterface $recipe, string $name, $repeat = 1, int $position = null): RecipeInterface;

    /**
     * To define the excepted dish attempted at the end.
     *
     * @param DishInterface $dish
     * @return RecipeInterface
     */
    public function given(DishInterface $dish): RecipeInterface;

    /**
     * To train a chef about this recipe.
     *
     * @param ChefInterface $chef
     * @return RecipeInterface
     */
    public function train(ChefInterface $chef): RecipeInterface;

    /**
     * To prepare the work plan of the chef before start the cooking.
     *
     * @param array $workPlan
     * @param ChefInterface $chef
     * @return RecipeInterface
     */
    public function prepare(array &$workPlan, ChefInterface $chef): RecipeInterface;

    /**
     * To validate the result of the cooking via the dish defined via the method "given".
     *
     * @param $value
     * @return RecipeInterface
     */
    public function validate($value): RecipeInterface;
}
