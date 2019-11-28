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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Chef implements ProxyInterface, AutomatedInterface, ChefInterface
{
    use ProxyTrait,
        AutomatedTrait;

    private array $workPlan = [];

    /**
     * @var BowlInterface[]
     */
    private array $steps = [];

    /**
     * @var BowlInterface[]
     */
    private array $onError = [];

    /**
     * @var BowlInterface[]
     */
    private array $stepsNames = [];

    private RecipeInterface $recipe;

    private array $missingIngredients = [];

    private int $position = 0;

    private bool $cooking = false;

    /**
     * @inheritDoc
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
     * @inheritDoc
     */
    protected function listAssertions(): array
    {
        return [
            (new Property(Free::class))->with('recipe', new IsNull()),
            (new Property(Free::class))->with('steps', new IsEqual([])),
            (new Property(Trained::class))
                ->with('recipe', new IsInstanceOf(RecipeInterface::class))
                ->with('steps', new IsNotEqual([])),
            (new Property(Cooking::class))
                ->with('recipe', new IsInstanceOf(RecipeInterface::class))
                ->with('steps', new IsNotEqual([]))
                ->with('cooking', new IsEqual(true)),
        ];
    }

    public function __construct(RecipeInterface $recipe = null)
    {
        $this->initializeProxy();

        $this->updateStates();

        if ($recipe instanceof RecipeInterface) {
            $this->read($recipe);
        }
    }

    /**
     * @inheritDoc
     */
    public function read(RecipeInterface $recipe): ChefInterface
    {
        return $this->readRecipe($recipe);
    }

    /**
     * @inheritDoc
     */
    public function reserveAndBegin(RecipeInterface $recipe): ChefInterface
    {
        return $this->begin($recipe);
    }

    /**
     * @inheritDoc
     */
    public function missing(IngredientInterface $ingredient, string $message): ChefInterface
    {
        return $this->missingIngredient($ingredient, $message);
    }

    /**
     * @inheritDoc
     */
    public function updateWorkPlan(array $with): ChefInterface
    {
        return $this->updateMyWorkPlan($with);
    }

    /**
     * @inheritDoc
     */
    public function followSteps(array $steps, $onError = []): ChefInterface
    {
        if ($onError instanceof BowlInterface) {
            //Avoid BC Break
            $onError = [$onError];
        }

        return $this->followStepsRecipe($steps, $onError);
    }

    /**
     * @inheritDoc
     */
    public function continue(array $with = [], string $nextStep = null): ChefInterface
    {
        return $this->continueRecipe($with, $nextStep);
    }

    /**
     * @inheritDoc
     */
    public function finish($result): ChefInterface
    {
        return $this->finishRecipe($result);
    }

    /**
     * @inheritDoc
     */
    public function process(array $workPlan): ChefInterface
    {
        return $this->runRecipe($workPlan);
    }
}
