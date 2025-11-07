<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
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
     * @param array<string, string|string[]|Value> $with
     */
    public function cook(
        callable | BowlInterface $action,
        string $name,
        array $with = [],
        RecipeRelativePositionEnum|int|null $position = null,
        ?string $offsetStepName = null,
    ): RecipeInterface;

    /*
     * To define / add a sub recipe into this recipe recipe. It will be wrapped into a RecipeBowl instance. The repeat
     * condition can be a callable and will be wrapped into a bowl in this case.
     */
    public function execute(
        BaseRecipeInterface $recipe,
        string $name,
        int | callable $repeat = 1,
        RecipeRelativePositionEnum|int|null $position = null,
        bool $inFiber = false,
        ?string $offsetStepName = null,
    ): RecipeInterface;

    /*
     * To define action when an error is occurred
     */
    public function onError(callable | BowlInterface $action): RecipeInterface;

    /*
     * To define the excepted dish attempted at the end.
     */
    public function given(DishInterface $dish): RecipeInterface;
}
