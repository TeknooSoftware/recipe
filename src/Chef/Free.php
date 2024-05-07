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

namespace Teknoo\Recipe\Chef;

use SensitiveParameter;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\States\State\StateInterface;
use Teknoo\States\State\StateTrait;

use function array_flip;
use function array_keys;
use function array_values;

/**
 * @see Chef
 *
 * State representing an untrained chef
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @mixin Chef
 */
class Free implements StateInterface
{
    use StateTrait;

    /*
     * To read and lean a recipe.
     */
    private function readRecipe(): callable
    {
        return function (BaseRecipeInterface $recipe): ChefInterface {
            /**
             * @var Chef $this
             */
            $this->recipe = $recipe->train($this);

            $this->updateStates();

            return $this;
        };
    }

    /**
     * To learn steps in the recipe, in the good order
     *
     * @param array<BowlInterface> $steps
     * @param array<BowlInterface> $onError
     */
    private function followStepsRecipe(): callable
    {
        return function (#[SensitiveParameter] array $steps, array $onError): ChefInterface {
            /**
             * @var Chef $this
             */
            $this->steps = array_values($steps);
            $this->stepsNames = array_flip(array_keys($steps));

            if (!empty($onError)) {
                $this->onError = $onError;
            }

            $this->updateStates();

            return $this;
        };
    }
}
