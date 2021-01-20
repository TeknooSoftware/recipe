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

namespace Teknoo\Recipe;

use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Chef\Cooking;
use Teknoo\Recipe\Chef\Free;
use Teknoo\Recipe\Chef\Trained;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\States\Automated\Assertion\AssertionInterface;
use Teknoo\States\Automated\Assertion\Property;
use Teknoo\States\Automated\Assertion\Property\IsNull;
use Teknoo\States\Automated\Assertion\Property\IsInstanceOf;
use Teknoo\States\Automated\Assertion\Property\IsEqual;
use Teknoo\States\Automated\Assertion\Property\IsNotEqual;
use Teknoo\States\Automated\AutomatedInterface;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Proxy\ProxyInterface;
use Teknoo\States\Proxy\ProxyTrait;

/**
 * Default implementation of chefs able to learn a recipe and execute. It will follow the recipe, like all algorithms.
 *
 * @see ChefInterface
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @method ChefInterface readRecipe(RecipeInterface $recipe)
 * @method ChefInterface followStepsRecipe(array $steps, array $onError)
 * @method ChefInterface updateMyWorkPlan(array $with)
 * @method ChefInterface runRecipe(array $workPlan)
 * @method ChefInterface begin(RecipeInterface $recipe)
 * @method ChefInterface missingIngredient(IngredientInterface $ingredient, string $message)
 * @method ChefInterface continueRecipe(array $with = [], string $nextStep = null)
 * @method ChefInterface finishRecipe($result)
 */
class Chef implements ProxyInterface, AutomatedInterface, ChefInterface
{
    use ProxyTrait;
    use AutomatedTrait;

    /**
     * @var array<string, mixed>
     */
    private array $workPlan = [];

    /**
     * @var array<BowlInterface>
     */
    private array $steps = [];

    /**
     * @var array<BowlInterface>
     */
    private array $onError = [];

    /**
     * @var array<int, string>
     */
    private array $stepsNames = [];

    private BaseRecipeInterface $recipe;

    /**
     * @var array<string, IngredientInterface>
     */
    private array $missingIngredients = [];

    private int $position = 0;

    private bool $cooking = false;

    /**
     * @return array<string>
     */
    protected static function statesListDeclaration(): array
    {
        return [
            Cooking::class,
            Free::class,
            Trained::class,
        ];
    }

    /**
     * @return array<AssertionInterface>
     */
    protected function listAssertions(): array
    {
        return [
            (new Property(Free::class))->with('recipe', new IsNull()),
            (new Property(Free::class))->with('steps', new IsEqual([])),
            (new Property(Trained::class))
                ->with('recipe', new IsInstanceOf(BaseRecipeInterface::class))
                ->with('steps', new IsNotEqual([])),
            (new Property(Cooking::class))
                ->with('recipe', new IsInstanceOf(BaseRecipeInterface::class))
                ->with('steps', new IsNotEqual([]))
                ->with('cooking', new IsEqual(true)),
        ];
    }

    final public function __construct(BaseRecipeInterface $recipe = null)
    {
        $this->initializeStateProxy();

        $this->updateStates();

        if ($recipe instanceof BaseRecipeInterface) {
            $this->read($recipe);
        }
    }

    public function read(BaseRecipeInterface $recipe): ChefInterface
    {
        if ($recipe instanceof RecipeInterface) {
            return $this->readRecipe($recipe);
        }

        $recipe->train($this);

        return $this;
    }

    public function reserveAndBegin(BaseRecipeInterface $recipe): ChefInterface
    {
        return $this->begin($recipe);
    }

    public function missing(IngredientInterface $ingredient, string $message): ChefInterface
    {
        return $this->missingIngredient($ingredient, $message);
    }

    public function updateWorkPlan(array $with): ChefInterface
    {
        return $this->updateMyWorkPlan($with);
    }

    /**
     * @array $steps
     * @param BowlInterface|array $onError
     */
    public function followSteps(array $steps, $onError = []): ChefInterface
    {
        if ($onError instanceof BowlInterface) {
            //Avoid BC Break
            $onError = [$onError];
        }

        return $this->followStepsRecipe($steps, $onError);
    }

    public function continue(array $with = [], string $nextStep = null): ChefInterface
    {
        return $this->continueRecipe($with, $nextStep);
    }

    public function finish($result): ChefInterface
    {
        return $this->finishRecipe($result);
    }

    public function error(\Throwable $error): ChefInterface
    {
        return $this->errorInRecipe($error);
    }

    public function process(array $workPlan): ChefInterface
    {
        return $this->runRecipe($workPlan);
    }
}
