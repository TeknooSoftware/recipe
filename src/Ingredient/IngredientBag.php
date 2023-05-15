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

namespace Teknoo\Recipe\Ingredient;

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;

/**
 * Bag definition to bring all prepared ingredient to pass to the chef in a single pass to the chef without call
 * several time updateWorkPlan
 *
 * @see IngredientBagInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class IngredientBag implements IngredientBagInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $workPlan = [];

    public function set(string $name, mixed $value): IngredientBagInterface
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
