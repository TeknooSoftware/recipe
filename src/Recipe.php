<?php

declare(strict_types=1);

/**
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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Recipe;

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\Recipe\Draft;
use Teknoo\Recipe\Recipe\Written;
use Teknoo\States\Automated\Assertion\Property;
use Teknoo\States\Automated\Assertion\Property\IsNull;
use Teknoo\States\Automated\Assertion\Property\IsNotNull;
use Teknoo\States\Automated\AutomatedInterface;
use Teknoo\States\Automated\AutomatedTrait;
use Teknoo\States\Proxy\ProxyInterface;
use Teknoo\States\Proxy\ProxyTrait;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Recipe implements ProxyInterface, AutomatedInterface, RecipeInterface
{
    use ImmutableTrait,
        ProxyTrait,
        AutomatedTrait;

    /**
     * @var IngredientInterface[]
     */
    private $requiredIngredients=[];

    /**
     * @var DishInterface
     */
    private $exceptedDish;

    /**
     * @var callable[]
     */
    private $steps = [];

    /**
     * @var callable[]
     */
    private $compiled;

    /**
     * Recipe constructor.
     */
    public function __construct()
    {
        $this->uniqueConstructorCheck();

        $this->initializeProxy();

        $this->updateStates();
    }

    /**
     * @inheritDoc
     */
    public function __clone()
    {
        $this->cloneProxy();
    }

    /**
     * @inheritDoc
     */
    protected static function statesListDeclaration(): array
    {
        return [
            Draft::class,
            Written::class,
        ];
    }

    /**
     * @inheritDoc
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


    /**
     * @inheritDoc
     */
    public function require(IngredientInterface $ingredient): RecipeInterface
    {
        return $this->addIngredient($ingredient);
    }

    /**
     * @inheritDoc
     */
    public function do(callable $action, array $with = [], int $position = null): RecipeInterface
    {
        return $this->addStep($action, $with, $position);
    }

    /**
     * @inheritDoc
     */
    public function given(DishInterface $dish): RecipeInterface
    {
        return $this->setExceptedDish($dish);
    }

    private function browseSteps()
    {
        $steps = $this->steps;
        ksort($steps);

        foreach ($this->steps as &$stepsSublist) {
            foreach ($stepsSublist as &$step) {
                yield $step;
            }
        }
    }

    private function compileStep()
    {
        /**
         * @var Recipe $this
         */
        if (empty($this->compiled)) {
            $this->compiled = [];
            foreach ($this->browseSteps() as $step) {
                $this->compiled[] = $step;
            }

            $this->updateStates();
        }

        return $this->compiled;
    }

    /**
     * @inheritDoc
     */
    public function train(ChefInterface $chef): RecipeInterface
    {
        $that = $this->cloneMe();

        $chef->followSteps($that->compileStep());

        return $that;
    }

    /**
     * {@inheritdoc}
     */
    public function prepare(array &$workPlan, ChefInterface $chef): RecipeInterface
    {
        return $this->prepareCooking($workPlan, $chef);
    }

    /**
     * @inheritDoc
     */
    public function validate($value): RecipeInterface
    {
        return $this->validateDish($value);
    }
}
