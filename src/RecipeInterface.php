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
 * of the recipe. When a chef learn, the recipe returned must be frozen and not accept any new step or ingredient.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface RecipeInterface extends ImmutableInterface, BaseRecipeInterface
{
    /*
     * To define required ingredients to start the cooking of the recipe.
     */
    public function require(IngredientInterface $ingredient): RecipeInterface;

    /**
     * To define actions to realize the recipe.
     *
     * @param array<string, string> $with
     */
    public function cook(
        callable | BowlInterface $action,
        string $name,
        array $with = [],
        int $position = null
    ): RecipeInterface;

    /*
     * To define action when an error is occurred
     */
    public function onError(callable | BowlInterface $action): RecipeInterface;

    /*
     * To define / add a sub recipe into this recipe recipe. It will be wrapped into a RecipeBowl instance. The repeat
     * condition can be a callable and will be wrapped into a bowl in this case.
     */
    public function execute(
        BaseRecipeInterface $recipe,
        string $name,
        int | callable $repeat = 1,
        int $position = null
    ): RecipeInterface;

    /*
     * To define the excepted dish attempted at the end.
     */
    public function given(DishInterface $dish): RecipeInterface;
}
