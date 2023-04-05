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
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe;

/**
 * Base interface common to Cookbook and Recipe to define all methods needed to execute and validate a recipe.
 * Recipe contains methods to define / write a dynamic recipes, Cookbook to execute and customize static recipes
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface BaseRecipeInterface
{
    /*
     * To train a chef about this recipe.
     */
    public function train(ChefInterface $chef): BaseRecipeInterface;

    /**
     * To prepare the work plan of the chef before start the cooking.
     *
     * @param array<string, mixed> $workPlan
     */
    public function prepare(array &$workPlan, ChefInterface $chef): BaseRecipeInterface;

    /*
     * To validate the result of the cooking via the dish defined via the method "given".
     */
    public function validate(mixed $value): BaseRecipeInterface;
}
