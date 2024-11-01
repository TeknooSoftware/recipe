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

namespace Teknoo\Tests\Recipe;

use TypeError;
use stdClass;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\RecipeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractRecipeTests extends TestCase
{
    abstract public function buildRecipe(): RecipeInterface;

    public function testExceptionOnRequireWithBadIngredient(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRecipe()->require(new stdClass());
    }

    public function testRequire(): void
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

    public function testExceptionOnCookWithNotCallable(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRecipe()->cook(new stdClass(), 'foo');
    }

    public function testExceptionOnCookWithBadName(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRecipe()->cook(function (): void {
        }, new stdClass());
    }

    public function testExceptionOnCookWithBadParameterMapping(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRecipe()->cook(function (): void {
        }, 'foo', new stdClass());
    }

    public function testExceptionOnCookWithBadPosition(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRecipe()->cook(function (): void {
        }, 'foo', ['foo' => 'bar'], new stdClass());
    }

    public function testCookWithDefaultMapping(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->cook(
            function (): void {
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

    public function testCook(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->cook(
            function (): void {
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

    public function testOnError(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithError = $recipe->onError(
            function (): void {
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

    public function testExceptionOnErrorWithNotCallable(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRecipe()->onError(new stdClass());
    }


    public function testExceptionOnExecuteWithNotRecipe(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRecipe()->execute(new stdClass(), 'foo');
    }

    public function testExceptionOnExecuteWithBadName(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRecipe()->execute($this->createMock(RecipeInterface::class), new stdClass());
    }

    public function testExceptionOnExecuteWithBadPosition(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRecipe()->execute($this->createMock(RecipeInterface::class), 'foo', 123, new stdClass());
    }

    public function testExecuteWithRecipe(): void
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

    public function testExecuteWithRecipeInFiber(): void
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

    public function testExecuteWithBaseRecipe(): void
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

    public function testExecuteWithCookbook(): void
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

    public function testExecuteWithRepeatAsNumber(): void
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

    public function testExecuteWithRepeatAsCallable(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->execute(
            $this->createMock(RecipeInterface::class),
            'foo',
            function (): void {
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

    public function testExceptionOnGivenWithBadDish(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRecipe()->given(new stdClass());
    }

    public function testGiven(): void
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

    public function testDish(): void
    {
        $dish = $this->createMock(DishInterface::class);
        $dish->expects($this->once())
            ->method('isExcepted')
            ->with('fooBar')
            ->willReturnSelf();

        self::assertInstanceOf(
            RecipeInterface::class,
            $this->buildRecipe()
                ->given($dish)
                ->cook(function (): void {
                }, 'foo')
                ->train($this->createMock(ChefInterface::class))
                ->validate('fooBar')
        );
    }

    public function testValidateDishWithoutDish(): void
    {
        self::assertInstanceOf(
            RecipeInterface::class,
            $this->buildRecipe()
                ->cook(function (): void {
                }, 'foo')
                ->train($this->createMock(ChefInterface::class))
                ->validate('fooBar')
        );
    }

    public function testExceptionOnTrainWithBadIngredient(): void
    {
        $this->expectException(TypeError::class);
        $this->buildRecipe()->train(new stdClass());
    }

    public function testTrainEmpty(): void
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects($this->once())
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

    public function testExceptionOnPrepareWithABadWorkPlan(): void
    {
        $this->expectException(TypeError::class);
        $workPlan = new stdClass();
        $this->buildRecipe()->prepare($workPlan, $this->createMock(ChefInterface::class));
    }

    public function testExceptionOnPrepareWithABadChef(): void
    {
        $this->expectException(TypeError::class);
        $array = ['foo' => 'bar'];
        $this->buildRecipe()->prepare($array, new stdClass());
    }

    public function testPrepare(): void
    {
        $chef = $this->createMock(ChefInterface::class);
        $ingredient = $this->createMock(IngredientInterface::class);
        $workPlan = ['foo' => 'bar'];

        $ingredient->expects($this->once())
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
            $recipe = $recipe->cook(function (): void {
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
