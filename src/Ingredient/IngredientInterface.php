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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Ingredient;

use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Recipe\ChefInterface;

/**
 * Interface to define required ingredient needed to start cooking a recipe, initialize or clean them if it's necessary.
 * Ingredient must be immutable.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface IngredientInterface extends ImmutableInterface
{
    /**
     * To check if an ingredient is available on the workplan and inject the cleaned ingredient into the workplan.
     * If the ingredient is not available, the instance must call the method missing of the chef.
     *
     * @param array<string, mixed> $workPlan
     */
    public function prepare(
        array $workPlan,
        ChefInterface $chef,
        ?IngredientBagInterface $bag = null
    ): IngredientInterface;
}
