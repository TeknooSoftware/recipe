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

namespace Teknoo\Recipe\Recipe;

use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientBag;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * State representing a finished recipe, able to train chef and validate theirs cooking.
 * @see Recipe
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @mixin Recipe
 */
class Written implements StateInterface
{
    use StateTrait;

    /*
     * To check if all ingredients are available and valid on the workplan
     */
    public function prepareCooking(): callable
    {
        return function (array $workPlan, ChefInterface $chef): RecipeInterface {
            /**
             * @var Recipe $this
             */
            $bag = new IngredientBag();
            foreach ($this->requiredIngredients as $ingredient) {
                $ingredient->prepare($workPlan, $chef, $bag);
            }

            $bag->updateWorkPlan($chef);

            return $this;
        };
    }

    /*
     * To validate the result of the cooking.
     */
    public function validateDish(): callable
    {
        return function (mixed $value): RecipeInterface {
            /**
             * @var Recipe $this
             */
            if ($this->exceptedDish instanceof DishInterface) {
                $this->exceptedDish->isExcepted($value);
            }

            return $this;
        };
    }
}
