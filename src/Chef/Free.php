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

use Teknoo\Recipe\BaseRecipeInterface;
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
class Free implements StateInterface
{
    use StateTrait;

    /*
     * To read and lean a recipe.
     */
    public function readRecipe(): callable
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

    /*
     * To learn steps in the recipe, in the good order
     */
    public function followStepsRecipe(): callable
    {
        return function (array $steps, array $onError): ChefInterface {
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
