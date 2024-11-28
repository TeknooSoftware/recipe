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

namespace Teknoo\Recipe\Plan;

use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\PlanInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * Base trait to implement quickly a plan and manage a shared recipe without implement all methods defined in
 * the PlanInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait BasePlanTrait
{
    private bool $recipePopulated = false;

    private RecipeInterface $recipe;

    /**
     * @var array<string, mixed>
     */
    private array $defaultWorkplan = [];

    abstract protected function populateRecipe(RecipeInterface $recipe): RecipeInterface;

    private function doPopulatingOfRecipe(RecipeInterface $recipe): RecipeInterface
    {
        return $this->populateRecipe($recipe);
    }

    private function getRecipe(): RecipeInterface
    {
        if ($this->recipePopulated) {
            return $this->recipe;
        }

        $this->recipe = $this->doPopulatingOfRecipe($this->recipe);
        $this->recipePopulated = true;

        return $this->recipe;
    }

    public function train(ChefInterface $chef): BaseRecipeInterface
    {
        $chef->read($this->getRecipe());

        $chef->updateWorkPlan(
            $this->defaultWorkplan
            +
            [PlanInterface::class => $this]
        );

        return $this;
    }

    public function prepare(array &$workPlan, ChefInterface $chef): BaseRecipeInterface
    {
        $plan = [CookbookInterface::class => $this, PlanInterface::class => $this];
        $final = $workPlan + $this->defaultWorkplan + $plan;

        $this->getRecipe()->prepare($final, $chef);

        return $this;
    }

    public function validate($value): BaseRecipeInterface
    {
        $this->getRecipe()->validate($value);

        return $this;
    }

    public function fill(RecipeInterface $recipe): PlanInterface
    {
        $this->recipe = $recipe;
        $this->recipePopulated = false;

        return $this;
    }

    public function addToWorkplan(string $key, $value): self
    {
        $this->defaultWorkplan[$key] = $value;

        return $this;
    }
}
