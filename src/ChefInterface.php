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

namespace Teknoo\Recipe;

use RuntimeException;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\Ingredient\MergeableInterface;
use Throwable;

/**
 * Interface to define a chef able to learn a recipe and execute. It will follow the recipe, like all algorithms.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface ChefInterface
{
    /*
     * To read and learn a recipe.
     */
    public function read(BaseRecipeInterface $recipe): ChefInterface;

    /*
     * To reserve the current recipe to begin a sub recipe with the actual workplan.
     */
    public function reserveAndBegin(
        BaseRecipeInterface $recipe,
        ?CookingSupervisorInterface $supervisor = null,
    ): ChefInterface;

    /*
     * To known when an ingredient missing in the work plan to start the cooking
     */
    public function missing(IngredientInterface $ingredient, string $message): ChefInterface;

    /**
     * To update the work plan from ingredient.
     *
     * @param array<string, mixed> $with
     */
    public function updateWorkPlan(array $with): ChefInterface;

    /**
     * To merge an ingredient in the workplan with another ingredient of the same class
     *
     * @throws RuntimeException when the ingredient in the workplan is not mergeable.
     */
    public function merge(string $name, MergeableInterface $value): ChefInterface;

    /**
     * To remove from the work plan some ingredients.
     *
     * @param string $ingredients
     */
    public function cleanWorkPlan(...$ingredients): ChefInterface;

    /**
     * To learn steps to able to cook the recipe.
     *
     * @param array<BowlInterface> $steps
     * @param array<BowlInterface>|BowlInterface $onError
     */
    public function followSteps(array $steps, array | BowlInterface $onError = []): ChefInterface;

    /**
     * To continue to cook the recipe and execute the next step, but before complete the workp lan
     * with this new ingredient.
     *
     * @param array<string, mixed> $with
     */
    public function continue(array $with = [], string $nextStep = null): ChefInterface;

    /*
     * To interrupt cooking, without execute dish validation
     */
    public function interruptCooking(): ChefInterface;

    /*
     * To stop the error reporting to the top chef when an error is occured
     */
    public function stopErrorReporting(): ChefInterface;

    /*
     * To stop / finish cooking the recipe and check the result.
     */
    public function finish(mixed $result): ChefInterface;

    /*
     * To stop / finish cooking the recipe and check the result.
     */
    public function error(Throwable $error): ChefInterface;

    /*
     * To start cooking a recipe with an initial work plan.
     */
    /**
     * @param array<string, mixed> $workPlan
     */
    public function process(array $workPlan): ChefInterface;
}
