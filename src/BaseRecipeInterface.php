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
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Recipe;

use Teknoo\Immutable\ImmutableInterface;

/**
 * @copyright   Copyright (c) 2009-2020 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface BaseRecipeInterface
{
    /**
     * To train a chef about this recipe.
     *
     * @param ChefInterface $chef
     * @return RecipeInterface
     */
    public function train(ChefInterface $chef): BaseRecipeInterface;

    /**
     * To prepare the work plan of the chef before start the cooking.
     *
     * @param array<string, mixed> $workPlan
     * @param ChefInterface $chef
     * @return RecipeInterface
     */
    public function prepare(array &$workPlan, ChefInterface $chef): BaseRecipeInterface;

    /**
     * To validate the result of the cooking via the dish defined via the method "given".
     *
     * @param mixed $value
     * @return RecipeInterface
     */
    public function validate($value): BaseRecipeInterface;
}
