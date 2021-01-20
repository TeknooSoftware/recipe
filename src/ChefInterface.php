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

use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;

/**
 * Interface to define a chef able to learn a recipe and execute. It will follow the recipe, like all algorithms.
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface ChefInterface
{
    /**
     * To read and learn a recipe.
     *
     * @param BaseRecipeInterface $recipe
     * @return ChefInterface
     */
    public function read(BaseRecipeInterface $recipe): ChefInterface;

    /**
     * To reserve the current recipe to begin a sub recipe with the actual workplan.
     *
     * @param BaseRecipeInterface $recipe
     * @return ChefInterface
     */
    public function reserveAndBegin(BaseRecipeInterface $recipe): ChefInterface;

    /**
     * To known when an ingredient missing in the work plan to start the cooking
     *
     * @param IngredientInterface $ingredient
     * @param string $message
     * @return ChefInterface
     */
    public function missing(IngredientInterface $ingredient, string $message): ChefInterface;

    /**
     * To update the work plan from ingredient.
     *
     * @param array<string, mixed> $with
     * @return ChefInterface
     */
    public function updateWorkPlan(array $with): ChefInterface;

    /**
     * To learn steps to able to cook the recipe.
     *
     * @param array<BowlInterface> $steps
     * @param array<BowlInterface>|BowlInterface $onError
     * @return ChefInterface
     */
    public function followSteps(array $steps, /* BowlInterface */ $onError = []): ChefInterface;

    /**
     * To continue to cook the recipe and execute the next step, but before complete the workp lan
     * with this new ingredient.
     *
     * @param array<string, mixed> $with
     * @param string|null $nextStep
     * @return ChefInterface
     */
    public function continue(array $with = [], string $nextStep = null): ChefInterface;

    /**
     * To stop / finish cooking the recipe and check the result.
     *
     * @param mixed $result
     * @return ChefInterface
     */
    public function finish($result): ChefInterface;

    /**
     * To stop / finish cooking the recipe and check the result.
     *
     * @param \Throwable $error
     * @return ChefInterface
     */
    public function error(\Throwable $error): ChefInterface;

    /**
     * To start cooking a recipe with an initial work plan.
     *
     * @param array<string, mixed> $workPlan
     * @return ChefInterface
     */
    public function process(array $workPlan): ChefInterface;
}
