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

namespace Teknoo\Recipe\Ingredient;

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;

/**
 * Bag definition to bring all prepared ingredient to pass to the chef in a single pass to the chef without call
 * several time updateWorkPlan
 *
 * @see IngredientBagInterface
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class IngredientBag implements IngredientBagInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $workPlan = [];

    public function set(string $name, $value): IngredientBagInterface
    {
        $this->workPlan[$name] = $value;

        return $this;
    }

    public function updateWorkPlan(ChefInterface $chef): IngredientBagInterface
    {
        $chef->updateWorkPlan($this->workPlan);

        return $this;
    }
}
