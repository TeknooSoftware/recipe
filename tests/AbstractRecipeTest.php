<?php

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

namespace Teknoo\Tests;

use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\RecipeInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractRecipeTest extends TestCase
{
    abstract public function buildRecipe(): RecipeInterface;

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnRequireWithBadIngredient()
    {
        $this->buildRecipe()->require(new \stdClass());
    }

    public function testRequire()
    {
        $recipe = $this->buildRecipe();
        $recipeWithIngredient = $recipe->require(
            $this->createMock(IngredientInterface::class)
        );

        self::assertInstanceOf(
            RecipeInterface::class,
            $recipeWithIngredient
        );

        self::assertNotSame(
            $recipe,
            $recipeWithIngredient
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnDoWithNotCallable()
    {
        $this->buildRecipe()->do(new \stdClass());
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnDoWithBadParameterMapping()
    {
        $this->buildRecipe()->do(function(){}, new \stdClass());
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnDoWithBadPosition()
    {
        $this->buildRecipe()->do(function(){}, ['foo'=>'bar'], new \stdClass());
    }

    public function testDoWithDefaultMapping()
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->do(
            function () {}
        );

        self::assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStep
        );

        self::assertNotSame(
            $recipe,
            $recipeWithStep
        );
    }

    public function testDo()
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->do(
            function () {},
            ['foo' => 'bar'],
            123
        );

        self::assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStep
        );

        self::assertNotSame(
            $recipe,
            $recipeWithStep
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnGivenWithBadDish()
    {
        $this->buildRecipe()->given(new \stdClass());
    }

    public function testGiven()
    {
        $recipe = $this->buildRecipe();
        $recipeWithDish = $recipe->given(
            $this->createMock(DishInterface::class)
        );

        self::assertInstanceOf(
            RecipeInterface::class,
            $recipeWithDish
        );

        self::assertNotSame(
            $recipe,
            $recipeWithDish
        );
    }

    public function testDish()
    {
        $dish = $this->createMock(DishInterface::class);
        $dish->expects(self::once())
            ->method('isExcepted')
            ->with('fooBar')
            ->willReturnSelf();

        self::assertInstanceOf(
            RecipeInterface::class,
            $this->buildRecipe()
                ->given($dish)
                ->do(function(){})
                ->train($this->createMock(ChefInterface::class))
                ->validate('fooBar')
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnTrainWithBadIngredient()
    {
        $this->buildRecipe()->train(new \stdClass());
    }

    public function testTrain()
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects(self::once())
            ->method('followSteps')
            ->willReturnSelf();

        self::assertInstanceOf(
            RecipeInterface::class,
            $this->buildRecipe()->train(
                $chef
            )
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnPrepareWithABadWorkPlan()
    {
        $workPlan = new \stdClass();
        $this->buildRecipe()->prepare($workPlan, $this->createMock(ChefInterface::class));
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnPrepareWithABadChef()
    {
        $array = ['foo'=>'bar'];
        $this->buildRecipe()->prepare($array, new \stdClass());
    }

    public function testPrepare()
    {
        $chef = $this->createMock(ChefInterface::class);
        $ingredient = $this->createMock(IngredientInterface::class);
        $workPlan = ['foo' => 'bar'];

        $ingredient->expects(self::once())
            ->method('prepare')
            ->with($workPlan, $chef)
            ->willReturnSelf();

        $recipe = $this->buildRecipe();

        self::assertInstanceOf(
            RecipeInterface::class,
            $recipe = $recipe->require($ingredient)
        );

        self::assertInstanceOf(
            RecipeInterface::class,
            $recipe = $recipe->do(function () {})
        );

        self::assertInstanceOf(
            RecipeInterface::class,
            $recipe = $recipe->train($chef)
        );

        self::assertInstanceOf(
            RecipeInterface::class,
            $recipe = $recipe->prepare($workPlan, $chef)
        );
    }
}