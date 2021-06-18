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

use RuntimeException;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\MergeableInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

use function array_diff_key;
use function array_flip;

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
            $this->workPlan = $with + $this->workPlan;

            return $this;
        };
    }

    /**
     * To update/prepare ingredients available on the workplan for the cooking
     */
    public function mergeInMyWorkPlan(): callable
    {
        return function (string $name, MergeableInterface $value): ChefInterface {
            if (!isset($this->workPlan[$name])) {
                $this->workPlan[$name] = $value;

                return $this;
            }

            if (!$this->workPlan[$name] instanceof MergeableInterface) {
                throw new RuntimeException("Error $name in the workplan is not mergeable");
            }

            $this->workPlan[$name]->merge($value);

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
     * To interrupt execution of all next steps, including next steps in top chef
     */
    private function interrupt(): callable
    {
        return function () {
            $this->position = count($this->steps) + 1;
            $this->cooking = false;

            $this->updateStates();

            return $this;
        };
    }

    /**
     * Internal method to clean the workplan after cooking.
     * @internal
     */
    private function clean(): callable
    {
        return function (): void {
            /**
             * @var Chef $this
             */
            $this->workPlan = [];
            $this->cooking = false;

            $this->updateStates();
        };
    }

    /**
     * To execute a cooking and switch to cookine state.
     */
    public function runRecipe(): callable
    {
        return function (array $workPlan): ChefInterface {
            //If this method is called, $this->recipe is a valid RecipeInterface instance
            $this->workPlan = $workPlan + $this->workPlan;
            $this->cooking = true;
            $this->errorReporing = true;

            $this->updateStates();

            $this->prepare();

            $this->continue();

            $this->clean();

            return $this;
        };
    }
}
