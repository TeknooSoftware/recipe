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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Cookbook;

use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * Base trait to implement quickly a cookbook and manage a shared recipe without implement all methods defined in
 * the CookbookInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait BaseCookbookTrait
{
    private bool $recipePopulated = false;

    private RecipeInterface $recipe;

    /**
     * @var array<string, mixed>
     */
    private array $defaultWorkplan = [];

    abstract protected function populateRecipe(RecipeInterface $recipe): RecipeInterface;

    private function getRecipe(): RecipeInterface
    {
        if ($this->recipePopulated) {
            return $this->recipe;
        }

        $this->recipe = $this->populateRecipe($this->recipe);
        $this->recipePopulated = true;

        return $this->recipe;
    }

    public function train(ChefInterface $chef): BaseRecipeInterface
    {
        $chef->read($this->getRecipe());

        $chef->updateWorkPlan(
            $this->defaultWorkplan
            +
            [CookbookInterface::class => $this]
        );

        return $this;
    }

    public function prepare(array &$workPlan, ChefInterface $chef): BaseRecipeInterface
    {
        $final = $workPlan + $this->defaultWorkplan + [CookbookInterface::class => $this];

        $this->getRecipe()->prepare($final, $chef);

        return $this;
    }

    public function validate($value): BaseRecipeInterface
    {
        $this->getRecipe()->validate($value);

        return $this;
    }

    public function fill(RecipeInterface $recipe): CookbookInterface
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
