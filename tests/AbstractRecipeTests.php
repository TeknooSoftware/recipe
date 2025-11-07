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

namespace Teknoo\Tests\Recipe;

use Teknoo\Recipe\RecipeRelativePositionEnum;
use TypeError;
use stdClass;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\PlanInterface;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\RecipeInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
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

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithIngredient
        );

        $this->assertNotSame(
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

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStep
        );

        $this->assertNotSame(
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

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStep
        );

        $this->assertNotSame(
            $recipe,
            $recipeWithStep
        );
    }

    public function testCookWithRelativePositionWithoutOffsetStepName(): void
    {
        $recipe = $this->buildRecipe();
        $this->expectException(\InvalidArgumentException::class);
        $recipeWithStep = $recipe->cook(
            function (): void {
            },
            'foo',
            ['foo' => 'bar'],
            RecipeRelativePositionEnum::After
        );
    }

    public function testCookWithRelativePositionAfterOffsetStepName(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->cook(
            action: fn() => true,
            name: 'foo',
            with: ['foo' => 'bar'],
            position: 123,
        );

        $recipeWithOrder = $recipeWithStep->cook(
            action: fn() => true,
            name: 'bar',
            with: ['foo' => 'bar'],
            position: RecipeRelativePositionEnum::After,
            offsetStepName: 'foo',
        );

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithOrder
        );

        $this->assertNotSame(
            $recipeWithStep,
            $recipeWithOrder
        );
    }

    public function testCookWithRelativePositionBeforeOffsetStepName(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->cook(
            action: fn() => true,
            name: 'foo',
            with: ['foo' => 'bar'],
            position: 123,
        );

        $recipeWithOrder = $recipeWithStep->cook(
            action: fn() => true,
            name: 'bar',
            with: ['foo' => 'bar'],
            position: RecipeRelativePositionEnum::Before,
            offsetStepName: 'foo',
        );

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithOrder
        );

        $this->assertNotSame(
            $recipeWithStep,
            $recipeWithOrder
        );
    }

    public function testCookWithRelativePositionWithOffsetStepNameNotFound(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->cook(
            action: fn() => true,
            name: 'foo',
            with: ['foo' => 'bar'],
            position: 123,
        );

        $recipeWithOrder = $recipeWithStep->cook(
            action: fn() => true,
            name: 'bar',
            with: ['foo' => 'bar'],
            position: RecipeRelativePositionEnum::After,
            offsetStepName: 'foo2',
        );

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithOrder
        );

        $this->assertNotSame(
            $recipeWithStep,
            $recipeWithOrder
        );
    }

    public function testOnError(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithError = $recipe->onError(
            function (): void {
            }
        );

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithError
        );

        $this->assertNotSame(
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

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStep
        );

        $this->assertNotSame(
            $recipe,
            $recipeWithStep
        );
    }

    public function testExecuteWithRecipeWithRelativePositionWithoutOffsetStepName(): void
    {
        $recipe = $this->buildRecipe();
        $this->expectException(\InvalidArgumentException::class);
        $recipe->execute(
            recipe: $this->createMock(RecipeInterface::class),
            name: 'foo',
            position: RecipeRelativePositionEnum::After,
        );
    }

    public function testExecuteWithRecipeWithRelativePositionAfterOffsetStepName(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->execute(
            recipe: $this->createMock(RecipeInterface::class),
            name: 'foo'
        );

        $recipeWithStepWithOffset = $recipeWithStep->execute(
            recipe: $this->createMock(RecipeInterface::class),
            name: 'bar',
            position: RecipeRelativePositionEnum::After,
            offsetStepName: 'foo',
        );

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStepWithOffset
        );

        $this->assertNotSame(
            $recipeWithStep,
            $recipeWithStepWithOffset
        );
    }

    public function testExecuteWithRecipeWithRelativePositionBeforeOffsetStepName(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->execute(
            recipe: $this->createMock(RecipeInterface::class),
            name: 'foo'
        );

        $recipeWithStepWithOffset = $recipeWithStep->execute(
            recipe: $this->createMock(RecipeInterface::class),
            name: 'bar',
            position: RecipeRelativePositionEnum::Before,
            offsetStepName: 'foo',
        );

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStepWithOffset
        );

        $this->assertNotSame(
            $recipeWithStep,
            $recipeWithStepWithOffset
        );
    }

    public function testExecuteWithRecipeWithRelativePositionOnOffsetStepNameNotFound(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->execute(
            recipe: $this->createMock(RecipeInterface::class),
            name: 'foo'
        );

        $recipeWithStepWithOffset = $recipeWithStep->execute(
            recipe: $this->createMock(RecipeInterface::class),
            name: 'bar',
            position: RecipeRelativePositionEnum::After,
            offsetStepName: 'foo2',
        );

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStepWithOffset
        );

        $this->assertNotSame(
            $recipeWithStep,
            $recipeWithStepWithOffset
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

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStep
        );

        $this->assertNotSame(
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

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStep
        );

        $this->assertNotSame(
            $recipe,
            $recipeWithStep
        );
    }

    public function testExecuteWithPlan(): void
    {
        $recipe = $this->buildRecipe();
        $recipeWithStep = $recipe->execute(
            $this->createMock(PlanInterface::class),
            'foo'
        );

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStep
        );

        $this->assertNotSame(
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

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStep
        );

        $this->assertNotSame(
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

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithStep
        );

        $this->assertNotSame(
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

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipeWithDish
        );

        $this->assertNotSame(
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

        $this->assertInstanceOf(
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
        $this->assertInstanceOf(
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

        $this->assertInstanceOf(
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

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipe = $recipe->require($ingredient)
        );

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipe = $recipe->cook(function (): void {
            }, 'foo')
        );

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipe = $recipe->train($chef)
        );

        $this->assertInstanceOf(
            RecipeInterface::class,
            $recipe = $recipe->prepare($workPlan, $chef)
        );
    }
}
