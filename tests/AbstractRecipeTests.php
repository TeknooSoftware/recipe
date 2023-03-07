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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe;

use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\RecipeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractRecipeTests extends TestCase
{
    abstract public function buildRecipe(): RecipeInterface;

    public function testExceptionOnRequireWithBadIngredient()
    {
        $this->expectException(\TypeError::class);
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

    public function testExceptionOnCookWithNotCallable()
    {
        $this->expectException(\TypeError::class);
        $this->buildRecipe()->cook(new \stdClass(), 'foo');
    }

    public function testExceptionOnCookWithBadName()
    {
        $this->expectException(\TypeError::class);
        $this->buildRecipe()->cook(function () {
        }, new \stdClass());
    }

    public function testExceptionOnCookWithBadParameterMapping()
    {
        $this->expectException(\TypeError::class);
        $this->buildRecipe()->cook(function () {
        }, 'foo', new \stdClass());
    }

    public function testExceptionOnCookWithBadPosition()
    {
        $this->expectException(\TypeError::class);
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

    public function testOnError()
    {
        $recipe = $this->buildRecipe();
        $recipeWithError = $recipe->onError(
            function () {
            }
        );

        self::assertInstanceOf(
            RecipeInterface::class,
            $recipeWithError
        );

        self::assertNotSame(
            $recipe,
            $recipeWithError
        );
    }

    public function testExceptionOnErrorWithNotCallable()
    {
        $this->expectException(\TypeError::class);
        $this->buildRecipe()->onError(new \stdClass());
    }


    public function testExceptionOnExecuteWithNotRecipe()
    {
        $this->expectException(\TypeError::class);
        $this->buildRecipe()->execute(new \stdClass(), 'foo');
    }

    public function testExceptionOnExecuteWithBadName()
    {
        $this->expectException(\TypeError::class);
        $this->buildRecipe()->execute($this->createMock(RecipeInterface::class), new \stdClass());
    }

    public function testExceptionOnExecuteWithBadPosition()
    {
        $this->expectException(\TypeError::class);
        $this->buildRecipe()->execute($this->createMock(RecipeInterface::class), 'foo', 123, new \stdClass());
    }

    public function testExecuteWithRecipe()
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->execute(
            $this->createMock(RecipeInterface::class),
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

    public function testExecuteWithRecipeInFiber()
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->execute(
            recipe: $this->createMock(RecipeInterface::class),
            name: 'foo',
            inFiber: true
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

    public function testExecuteWithBaseRecipe()
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->execute(
            $this->createMock(BaseRecipeInterface::class),
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

    public function testExecuteWithCookbook()
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->execute(
            $this->createMock(CookbookInterface::class),
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

    public function testExecuteWithRepeatAsNumber()
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->execute(
            $this->createMock(RecipeInterface::class),
            'foo',
            123,
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

    public function testExecuteWithRepeatAsCallable()
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->execute(
            $this->createMock(RecipeInterface::class),
            'foo',
            function () {
            },
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

    public function testExceptionOnGivenWithBadDish()
    {
        $this->expectException(\TypeError::class);
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

    public function testValidateDishWithoutDish()
    {
        self::assertInstanceOf(
            RecipeInterface::class,
            $this->buildRecipe()
                ->cook(function () {
                }, 'foo')
                ->train($this->createMock(ChefInterface::class))
                ->validate('fooBar')
        );
    }

    public function testExceptionOnTrainWithBadIngredient()
    {
        $this->expectException(\TypeError::class);
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

    public function testExceptionOnPrepareWithABadWorkPlan()
    {
        $this->expectException(\TypeError::class);
        $workPlan = new \stdClass();
        $this->buildRecipe()->prepare($workPlan, $this->createMock(ChefInterface::class));
    }

    public function testExceptionOnPrepareWithABadChef()
    {
        $this->expectException(\TypeError::class);
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
