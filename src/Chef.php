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

namespace Teknoo\Recipe;

use SensitiveParameter;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Chef\Cooking;
use Teknoo\Recipe\Chef\Free;
use Teknoo\Recipe\Chef\Trained;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\Ingredient\MergeableInterface;
use Teknoo\States\Automated\Assertion\AssertionInterface;
use Teknoo\States\Automated\Assertion\Property;
use Teknoo\States\Automated\Assertion\Property\IsNull;
use Teknoo\States\Automated\Assertion\Property\IsInstanceOf;
use Teknoo\States\Automated\Assertion\Property\IsEqual;
use Teknoo\States\Automated\Assertion\Property\IsNotEqual;
use Teknoo\States\Automated\AutomatedInterface;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Proxy\ProxyTrait;
use Throwable;

/**
 * Default implementation of chefs able to learn a recipe and execute. It will follow the recipe, like all algorithms.
 *
 * @see ChefInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Chef implements AutomatedInterface, ChefInterface
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
     * @var array<string, int>
     */
    private array $stepsNames = [];

    private BaseRecipeInterface $recipe;

    /**
     * @var array<string, IngredientInterface>
     */
    private array $missingIngredients = [];

    private int $position = 0;

    private bool $cooking = false;

    private bool $errorReporing = false;

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

    final public function __construct(
        ?BaseRecipeInterface $recipe = null,
        private readonly ?self $topChef = null,
        private CookingSupervisorInterface $cookingSupervisor = new CookingSupervisor(),
    ) {
        $this->initializeStateProxy();

        $this->updateStates();

        if ($recipe instanceof BaseRecipeInterface) {
            $this->read($recipe);
        }
    }

    /**
     * @return void
     */
    public function __clone()
    {
        if ($this->cookingSupervisor instanceof CookingSupervisorInterface) {
            $this->cookingSupervisor = clone $this->cookingSupervisor;
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

    public function reserveAndBegin(
        BaseRecipeInterface $recipe,
        ?CookingSupervisorInterface $supervisor = null,
    ): ChefInterface {
        return $this->begin($recipe, $supervisor);
    }

    public function missing(IngredientInterface $ingredient, string $message): ChefInterface
    {
        return $this->missingIngredient($ingredient, $message);
    }

    public function updateWorkPlan(#[SensitiveParameter] array $with): ChefInterface
    {
        return $this->updateMyWorkPlan($with);
    }

    public function merge(string $name, MergeableInterface $value): ChefInterface
    {
        return $this->mergeInMyWorkPlan($name, $value);
    }

    public function cleanWorkPlan(#[SensitiveParameter] ...$ingredients): ChefInterface
    {
        return $this->removeFromMyWorkPlan($ingredients);
    }

    public function followSteps(#[SensitiveParameter] array $steps, BowlInterface | array $onError = []): ChefInterface
    {
        if ($onError instanceof BowlInterface) {
            //Avoid BC Break
            $onError = [$onError];
        }

        return $this->followStepsRecipe($steps, $onError);
    }

    public function continue(#[SensitiveParameter] array $with = [], ?string $nextStep = null): ChefInterface
    {
        return $this->continueRecipe($with, $nextStep);
    }

    public function interruptCooking(): ChefInterface
    {
        return $this->interrupt();
    }

    public function stopErrorReporting(): ChefInterface
    {
        $this->errorReporing = false;

        return $this;
    }

    public function finish(mixed $result): ChefInterface
    {
        return $this->finishRecipe($result);
    }

    public function error(#[SensitiveParameter] Throwable $error): ChefInterface
    {
        return $this->errorInRecipe($error);
    }

    public function process(#[SensitiveParameter] array $workPlan): ChefInterface
    {
        return $this->runRecipe($workPlan);
    }
}
