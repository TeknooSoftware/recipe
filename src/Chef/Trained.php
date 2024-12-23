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
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Chef;

use SensitiveParameter;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\Chef\Exception\NotMergeableException;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\MergeableInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;
use Throwable;

use function array_diff_key;
use function array_flip;

/**
 * @see Chef
 *
 * State representing a trained chef with a recipe, able to execute it
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @mixin Chef
 */
class Trained implements StateInterface
{
    use StateTrait;

    /**
     * To update/prepare ingredients available on the workplan for the cooking
     * @param array<string, mixed> $with
     */
    private function updateMyWorkPlan(): callable
    {
        return function (#[SensitiveParameter] array $with): ChefInterface {
            $this->workPlan = $with + $this->workPlan;

            return $this;
        };
    }

    /*
     * To update/prepare ingredients available on the workplan for the cooking
     */
    private function mergeInMyWorkPlan(): callable
    {
        return function (string $name, MergeableInterface $value): ChefInterface {
            if (!isset($this->workPlan[$name])) {
                $this->workPlan[$name] = $value;

                return $this;
            }

            if (!$this->workPlan[$name] instanceof MergeableInterface) {
                throw new NotMergeableException("Error $name in the workplan is not mergeable");
            }

            $this->workPlan[$name]->merge($value);

            return $this;
        };
    }

    /**
     * To remove some ingredients from the workplan
     * @param array<int|string, string> $ingredients
     */
    private function removeFromMyWorkPlan(): callable
    {
        return function (#[SensitiveParameter] array $ingredients): ChefInterface {
            $this->workPlan = array_diff_key($this->workPlan, array_flip($ingredients));

            return $this;
        };
    }

    /*
     * To interrupt execution of all next steps, including next steps in top chef
     */
    private function interrupt(): callable
    {
        return function (): static {
            $this->position = count($this->steps) + 1;
            $this->cooking = false;

            $this->updateStates();

            return $this;
        };
    }

    /*
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
     * @param array<string, mixed> $workPlan
     */
    private function runRecipe(): callable
    {
        return function (#[SensitiveParameter] array $workPlan): ChefInterface {
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

    /*
     * To execute steps defined to handle error
     */
    private function callErrors(): callable
    {
        return function (#[SensitiveParameter] Throwable $error): void {
            $this->workPlan['exception'] = $error;

            if (empty($this->onError)) {
                throw $error;
            }

            foreach ($this->onError as $onError) {
                $onError->execute($this, $this->workPlan, $this->cookingSupervisor);
            }

            if (null !== $this->topChef && true === $this->errorReporing) {
                $this->topChef->callErrors($error);
            }

            $this->interruptCooking();
        };
    }
}
