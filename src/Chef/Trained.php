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

namespace Teknoo\Recipe\Chef;

use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

class Trained implements StateInterface
{
    use StateTrait;

    /**
     * @inheritDoc
     */
    public function runRecipe() {
        return function (array $workPlan): ChefInterface {
            /**
             * @var Chef $this
             */
            if (!$this->recipe instanceof RecipeInterface) {
                throw new \RuntimeException('Error, this chef is not trained for a recipe');
            }

            $this->updateWorkPlan($workPlan);

            $this->recipe->prepare($this->workPlan , $this);

            $this->checkMissingIngredients();

            return $this;
        };
    }
}