<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe;

use Generator;
use InvalidArgumentException;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\Recipe\Draft;
use Teknoo\Recipe\Recipe\Written;
use Teknoo\States\Attributes\Assertion\Property;
use Teknoo\States\Attributes\StateClass;
use Teknoo\States\Automated\Assertion\Property\IsNull;
use Teknoo\States\Automated\Assertion\Property\IsNotNull;
use Teknoo\States\Automated\AutomatedInterface;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Proxy\Exception\StateNotFound;
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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[StateClass(Draft::class)]
#[StateClass(Written::class)]
#[Property(Draft::class, ['compiled', IsNull::class])]
#[Property(Written::class, ['compiled', IsNotNull::class])]
class Recipe implements AutomatedInterface, RecipeInterface
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
        RecipeRelativePositionEnum|int|null $position = null,
        ?string $offsetStepName = null,
    ): RecipeInterface {
        return $this->addStep($action, $name, $with, $position, $offsetStepName);
    }

    public function execute(
        BaseRecipeInterface $recipe,
        string $name,
        int | callable $repeat = 1,
        RecipeRelativePositionEnum|int|null $position = null,
        bool $inFiber = false,
        ?string $offsetStepName = null,
    ): RecipeInterface {
        return $this->addSubRecipe($recipe, $name, $repeat, $position, $inFiber, $offsetStepName);
    }

    public function onError(callable | BowlInterface $action): RecipeInterface
    {
        return $this->setOnError($action);
    }

    public function given(DishInterface $dish): RecipeInterface
    {
        return $this->setExceptedDish($dish);
    }

    /**
     * To find the position (the key in the steps matrix) of a step from its name. Returns null if the step is not
     * defined in this recipe.
     */
    private function findStepPosition(string $name): ?int
    {
        foreach ($this->steps as $position => &$stepsPerPositions) {
            foreach ($stepsPerPositions as &$stepsSubList) {
                if (isset($stepsSubList[$name])) {
                    return $position;
                }
            }
        }

        return null;
    }

    /**
     * To convert a relative position (before or after another step) to an absolute position in the steps matrix.
     * Returns null when the step must be appended at the end of the recipe (no position, or offset step not found).
     *
     * @throws InvalidArgumentException when a relative position is used without offset step name
     */
    private function resolveStepPosition(
        RecipeRelativePositionEnum|int|null $position,
        ?string $offsetStepName,
    ): ?int {
        if (!$position instanceof RecipeRelativePositionEnum) {
            return $position;
        }

        if (null === $offsetStepName) {
            throw new InvalidArgumentException('The offset name is required to define a step position');
        }

        $offsetPlace = $this->findStepPosition($offsetStepName);
        if (null === $offsetPlace) {
            return null;
        }

        return match ($position) {
            RecipeRelativePositionEnum::Before => $offsetPlace - 1,
            RecipeRelativePositionEnum::After => $offsetPlace + 1,
        };
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
