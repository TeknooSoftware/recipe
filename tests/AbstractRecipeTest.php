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

namespace Teknoo\Tests\Recipe;

use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\RecipeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
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
    public function testExceptionOnCookWithNotCallable()
    {
        $this->buildRecipe()->cook(new \stdClass(), 'foo');
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnCookWithBadName()
    {
        $this->buildRecipe()->cook(function () {
        }, new \stdClass());
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnCookWithBadParameterMapping()
    {
        $this->buildRecipe()->cook(function () {
        }, 'foo', new \stdClass());
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnCookWithBadPosition()
    {
        $this->buildRecipe()->cook(function () {
        }, 'foo', ['foo'=>'bar'], new \stdClass());
    }

    public function testCookWithDefaultMapping()
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->cook(
            function () {
            },
            'foo'
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

    public function testCook()
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->cook(
            function () {
            },
            'foo',
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
                ->cook(function () {
                }, 'foo')
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

    public function testTrainEmpty()
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects(self::once())
            ->method('followSteps')
            ->with([])
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
            $recipe = $recipe->cook(function () {
            }, 'foo')
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
