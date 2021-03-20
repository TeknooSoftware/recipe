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

namespace Teknoo\Recipe\Chef;

use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

use function array_diff_key;
use function array_flip;
use function array_merge;

/**
 * @see Chef
 *
 * State representing a trained chef with a recipe, able to execute it
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @mixin Chef
 */
class Trained implements StateInterface
{
    use StateTrait;

    /**
     * To update/prepare ingredients available on the workplan for the cooking
     */
    public function updateMyWorkPlan(): callable
    {
        return function (array $with): ChefInterface {
            $this->workPlan = array_merge($this->workPlan, $with);

            return $this;
        };
    }

    /**
     * To remove some ingredients from the workplan
     */
    public function removeFromMyWorkPlan(): callable
    {
        return function (array $ingredients): ChefInterface {
            $this->workPlan = array_diff_key($this->workPlan, array_flip($ingredients));

            return $this;
        };
    }

    /**
     * To execute a cooking and switch to cookine state.
     */
    public function runRecipe(): callable
    {
        return function (array $workPlan): ChefInterface {
            //If this method is called, $this->recipe is a valid RecipeInterface instance
            $this->workPlan = array_merge($this->workPlan, $workPlan);
            $this->cooking = true;

            $this->updateStates();

            $this->prepare();

            $this->continue();

            $this->clean();

            return $this;
        };
    }
}
