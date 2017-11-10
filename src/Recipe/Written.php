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

namespace Teknoo\Recipe\Recipe;

use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Written implements StateInterface
{
    use StateTrait;

    /**
     * {@inheritdoc}
     */
    public function prepareCooking()
    {
        return function (array $workPlan, ChefInterface $chef): RecipeInterface {
            /**
             * @var Recipe $this
             */
            foreach ($this->requiredIngredients as $ingredient) {
                $ingredient->prepare($workPlan, $chef);
            }

            return $this;
        };
    }

    /**
     * @inheritDoc
     */
    public function validateDish()
    {
        return function ($value): RecipeInterface {
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
