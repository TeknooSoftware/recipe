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

use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Ingredient\IngredientInterface;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface ChefInterface
{
    /**
     * @param RecipeInterface $recipe
     * @return ChefInterface
     */
    public function read(RecipeInterface $recipe): ChefInterface;

    /**
     * @param IngredientInterface $ingredient
     * @param string $message
     * @return ChefInterface
     */
    public function missing(IngredientInterface $ingredient, string $message): ChefInterface;

    /**
     * @param array $with
     * @return ChefInterface
     */
    public function updateWorkPlan(array $with): ChefInterface;

    /**
     * @param Bowl[] $steps
     * @return ChefInterface
     */
    public function followSteps(array $steps): ChefInterface;

    /**
     * @param array $with
     * @return ChefInterface
     */
    public function continue(array $with=[]): ChefInterface;

    /**
     * @param mixed $result
     * @return ChefInterface
     */
    public function finish($result): ChefInterface;

    /**
     * @param array $workPlan
     * @return ChefInterface
     */
    public function process(array $workPlan): ChefInterface;
}
