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

namespace Teknoo\Recipe;

use Generator;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\Recipe\Draft;
use Teknoo\Recipe\Recipe\Written;
use Teknoo\States\Automated\Assertion\AssertionInterface;
use Teknoo\States\Automated\Assertion\Property;
use Teknoo\States\Automated\Assertion\Property\IsNull;
use Teknoo\States\Automated\Assertion\Property\IsNotNull;
use Teknoo\States\Automated\AutomatedInterface;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Proxy\Exception\StateNotFound;
use Teknoo\States\Proxy\ProxyInterface;
use Teknoo\States\Proxy\ProxyTrait;

use function current;
use function key;

/**
 * Default implementation to define a recipe. A recipe has several ordered steps (as callable).
 * It can have several required ingredients needed to start the cooking and the excepted dish attempted.
 *
 * A recipe instance must be immutable. Each call to this method must be performed on a clone and not update the state
 * of the recipe.
 * When a chef learn, the recipe returned must be frozen and not accept any new step or ingredient.
 *
 * @see RecipeInterface
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Recipe implements ProxyInterface, AutomatedInterface, RecipeInterface
{
    use ImmutableTrait;
    use ProxyTrait;
    use AutomatedTrait;

    /**
     * @var IngredientInterface[]
     */
    private array $requiredIngredients = [];

    private ?DishInterface $exceptedDish = null;

    /**
     * @var array<int, array<array<string, BowlInterface>>>
     */
    private array $steps = [];

    /**
     * @var array<BowlInterface>
     */
    private array $onError = [];

    /**
     * @var array<BowlInterface>
     */
    private array $compiled;

    /**
     * @throws StateNotFound
     */
    public function __construct()
    {
        $this->uniqueConstructorCheck();

        $this->initializeStateProxy();

        $this->updateStates();
    }

    /**
     * @throws StateNotFound
     */
    public function __clone()
    {
        $this->cloneProxy();
    }

    /**
     * @return array<string>
     */
    protected static function statesListDeclaration(): array
    {
        return [
            Draft::class,
            Written::class,
        ];
    }

    /**
     * @return array<AssertionInterface>
     */
    protected function listAssertions(): array
    {
        return [
            (new Property(Draft::class))->with('compiled', new IsNull()),
            (new Property(Written::class))->with('compiled', new IsNotNull()),
        ];
    }

    protected function cloneMe(): Recipe
    {
        return clone $this;
    }


    public function require(IngredientInterface $ingredient): RecipeInterface
    {
        return $this->addIngredient($ingredient);
    }

    public function cook(
        callable | BowlInterface $action,
        string $name,
        array $with = [],
        int $position = null
    ): RecipeInterface {
        return $this->addStep($action, $name, $with, $position);
    }

    public function onError(callable | BowlInterface $action): RecipeInterface
    {
        return $this->setOnError($action);
    }

    public function execute(
        BaseRecipeInterface $recipe,
        string $name,
        int | callable $repeat = 1,
        int $position = null
    ): RecipeInterface {
        return $this->addSubRecipe($recipe, $name, $repeat, $position);
    }

    public function given(DishInterface $dish): RecipeInterface
    {
        return $this->setExceptedDish($dish);
    }

    /**
     * To browse steps, stored into an ordered matrix as a single array
     *
     * @return Generator<string, BowlInterface>
     */
    private function browseSteps(): iterable
    {
        $steps = $this->steps;
        ksort($steps);

        foreach ($steps as &$stepsSublist) {
            foreach ($stepsSublist as &$step) {
                yield (string) key($step) => current($step);
            }
        }
    }

    /**
     * To compile all steps into a single array to allow chefs to follow them easier
     *
     * @return array<BowlInterface>
     */
    private function compileStep(): array
    {
        if (empty($this->compiled)) {
            $this->compiled = [];
            foreach ($this->browseSteps() as $name => $step) {
                $counter = 1;
                $originalName = $name;
                while (isset($this->compiled[$name])) {
                    $name = $originalName . $counter++;
                }
                $this->compiled[$name] = $step;
            }

            $this->updateStates();
        }

        return $this->compiled;
    }

    public function train(ChefInterface $chef): RecipeInterface
    {
        $that = $this->cloneMe();

        $chef->followSteps($that->compileStep(), $this->onError);

        return $that;
    }

    public function prepare(array &$workPlan, ChefInterface $chef): RecipeInterface
    {
        return $this->prepareCooking($workPlan, $chef);
    }

    public function validate(mixed $value): RecipeInterface
    {
        return $this->validateDish($value);
    }
}
