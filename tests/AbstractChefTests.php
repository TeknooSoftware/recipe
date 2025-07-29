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

use Teknoo\Recipe\PlanInterface;
use TypeError;
use stdClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;
use Exception;
use RuntimeException;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\Ingredient\MergeableInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractChefTests extends TestCase
{
    abstract public function buildChef(?CookingSupervisorInterface $cookingSupervisor = null): ChefInterface;

    public function testExceptionOnReadWithBadRecipe(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->read(new stdClass());
    }

    public function testReadWithRecipe(): void
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $recipe->expects($this->once())
            ->method('train')
            ->willReturnSelf();

        $this->assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->read($recipe)
        );
    }

    public function testReadWithPlan(): void
    {
        $recipe = $this->createMock(PlanInterface::class);
        $recipe->expects($this->once())
            ->method('train')
            ->willReturnSelf();

        $this->assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->read($recipe)
        );
    }

    public function testReadWithBaseRecipe(): void
    {
        $recipe = $this->createMock(BaseRecipeInterface::class);
        $recipe->expects($this->once())
            ->method('train')
            ->willReturnSelf();

        $this->assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->read($recipe)
        );
    }

    public function testExceptionOnReserveAndBeginWithBadRecipe(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->reserveAndBegin(new stdClass());
    }

    public function testReserveAndBeginChefWithBaseSupervisor(): void
    {
        $this->expectException(TypeError::class);
        $recipe = $this->createMock(BaseRecipeInterface::class);
        $this->buildChef()->reserveAndBegin($recipe, new stdClass());
    }

    public function testReserveAndBeginAvailableOnCookingWithRecipe(): void
    {
        $mainRecipe = $this->createMock(RecipeInterface::class);
        $mainRecipe->expects($this->once())
            ->method('train')
            ->willReturnSelf();

        $subRecipe = $this->createMock(RecipeInterface::class);
        $subRecipe->expects($this->once())
            ->method('train')
            ->willReturnCallback(function (ChefInterface $chef) use ($subRecipe): MockObject {
                $chef->followSteps(['substep' => $this->createMock(BowlInterface::class)]);

                return $subRecipe;
            });

        $chef = $this->buildChef();
        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->read($mainRecipe)
        );

        $step = $this->createMock(BowlInterface::class);
        $step->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function (ChefInterface $chef) use ($step, $subRecipe): MockObject {
                $this->assertInstanceOf(
                    ChefInterface::class,
                    $subchef = $chef->reserveAndBegin($subRecipe)
                );

                $this->assertNotSame(
                    $chef,
                    $subchef
                );

                return $step;
            });

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->followSteps(['step' => $step])
        );

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );
    }

    public function testReserveAndBeginAvailableOnCookingWithBaseRecipe(): void
    {
        $mainRecipe = $this->createMock(RecipeInterface::class);
        $mainRecipe->expects($this->once())
            ->method('train')
            ->willReturnSelf();

        $subRecipe = $this->createMock(BaseRecipeInterface::class);
        $subRecipe->expects($this->once())
            ->method('train')
            ->willReturnCallback(function (ChefInterface $chef) use ($subRecipe): MockObject {
                $chef->followSteps(['substep' => $this->createMock(BowlInterface::class)]);

                return $subRecipe;
            });

        $chef = $this->buildChef();
        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->read($mainRecipe)
        );

        $step = $this->createMock(BowlInterface::class);
        $step->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function (ChefInterface $chef) use ($step, $subRecipe): MockObject {
                $this->assertInstanceOf(
                    ChefInterface::class,
                    $subchef = $chef->reserveAndBegin($subRecipe, $this->createMock(CookingSupervisorInterface::class))
                );

                $this->assertNotSame(
                    $chef,
                    $subchef
                );

                return $step;
            });

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->followSteps(['step' => $step])
        );

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );
    }

    public function testReserveAndBeginAvailableOnCookingWithPlan(): void
    {
        $mainRecipe = $this->createMock(RecipeInterface::class);
        $mainRecipe->expects($this->once())
            ->method('train')
            ->willReturnSelf();

        $subRecipe = $this->createMock(PlanInterface::class);
        $subRecipe->expects($this->once())
            ->method('train')
            ->willReturnCallback(function (ChefInterface $chef) use ($subRecipe): MockObject {
                $chef->followSteps(['substep' => $this->createMock(BowlInterface::class)]);

                return $subRecipe;
            });

        $chef = $this->buildChef();
        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->read($mainRecipe)
        );

        $step = $this->createMock(BowlInterface::class);
        $step->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function (ChefInterface $chef) use ($step, $subRecipe): MockObject {
                $this->assertInstanceOf(
                    ChefInterface::class,
                    $subchef = $chef->reserveAndBegin($subRecipe, $this->createMock(CookingSupervisorInterface::class))
                );

                $this->assertNotSame(
                    $chef,
                    $subchef
                );

                return $step;
            });

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->followSteps(['step' => $step])
        );

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );
    }

    public function testReserveAndBeginOnNonTrainedChefWithRecipe(): void
    {
        $this->expectException(Throwable::class);
        $recipe = $this->createMock(RecipeInterface::class);
        $this->buildChef()->reserveAndBegin($recipe);
    }

    public function testReserveAndBeginOnNonTrainedChefWithPlan(): void
    {
        $this->expectException(Throwable::class);
        $recipe = $this->createMock(PlanInterface::class);
        $this->buildChef()->reserveAndBegin($recipe);
    }

    public function testReserveAndBeginOnNonTrainedChefWithBaseRecipe(): void
    {
        $this->expectException(Throwable::class);
        $recipe = $this->createMock(BaseRecipeInterface::class);
        $this->buildChef()->reserveAndBegin($recipe);
    }

    public function testExceptionOnMissingWithBadIngredient(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->missing(new stdClass(), 'fooBar');
    }

    public function testExceptionOnMissingWithBadMessage(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->missing(
            $this->createMock(IngredientInterface::class),
            new stdClass()
        );
    }

    public function testMissing(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl): MockObject {
                $called = true;
                $this->assertInstanceOf(
                    ChefInterface::class,
                    $chef->missing(
                        $this->createMock(IngredientInterface::class),
                        'fooBar'
                    )
                );

                return $bowl;
            });

        $chef->followSteps([$bowl]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );

        $this->assertTrue($called);
    }

    public function testExceptionOnUpdateWorkPlanWithBadArray(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->missing(new stdClass());
    }

    public function testUpdateWorkPlan(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->updateWorkPlan(
                ['foo' => 'bar']
            )
        );
    }

    public function testExceptionOnUpdateWorkPlanWithBadName(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->missing(new stdClass(), $this->createMock(MergeableInterface::class));
    }

    public function testExceptionOnUpdateWorkPlanWithBadValue(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->missing('foo', new stdClass());
    }

    public function testMerge(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([
            new Bowl(
                function ($foo): void {
                    $this->assertEquals(9, $foo->value);
                },
                [],
            )
        ]);

        $c1 = new class () implements MergeableInterface {
            public int $value = 0;

            public function __clone(): void
            {
                $this->value = 0;
            }

            public function merge(MergeableInterface $mergeable): MergeableInterface
            {
                $this->value += $mergeable->value;

                return $this;
            }
        };

        $c2 = clone $c1;
        $c3 = clone $c1;

        $c1->value = 1;
        $c2->value = 3;
        $c3->value = 5;

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->merge(
                'foo',
                $c1
            )
        );

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->merge(
                'foo',
                $c2
            )
        );

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->merge(
                'foo',
                $c3
            )
        );

        $chef->process([]);

        $this->assertEquals(9, $c1->value);
    }

    public function testMergeWithNonMergeable(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->updateWorkPlan(
                ['foo' => new stdClass()]
            )
        );

        $this->expectException(RuntimeException::class);
        $chef->merge(
            'foo',
            $this->createMock(MergeableInterface::class)
        );
    }

    public function testMergeWithNonMergeableAfterUpdate(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->updateWorkPlan(
                ['foo' => $this->createMock(MergeableInterface::class)]
            )
        );

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->updateWorkPlan(
                ['foo' => new stdClass()]
            )
        );

        $this->expectException(RuntimeException::class);
        $chef->merge(
            'foo',
            $this->createMock(MergeableInterface::class)
        );
    }

    public function testExceptionOnCleanWorkPlanWithBadArray(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->missing(new stdClass());
    }

    public function testCleanWorkPlan(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->cleanWorkPlan(
                'foo',
                'bar'
            )
        );
    }

    public function testExceptionOnFollowStepsWithBadArray(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->missing(new stdClass());
    }

    public function testFollowSteps(): void
    {
        $this->assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->followSteps(
                ['foo' => 'bar']
            )
        );
    }

    public function testFollowStepsWithErrorAsArray(): void
    {
        $this->assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->followSteps(
                ['foo' => 'bar'],
                ['foo2']
            )
        );
    }

    public function testFollowStepsWithErrorAsBowl(): void
    {
        $this->assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->followSteps(
                ['foo' => 'bar'],
                $this->createMock(BowlInterface::class)
            )
        );
    }

    public function testExceptionOnContinueWithBadArray(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->missing(new stdClass());
    }

    public function testExceptionOnContinueWithBadNextStep(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->missing([], new stdClass());
    }

    public function testContinue(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($chefPassed, $workPlan) use ($chef, &$called, $bowl): MockObject {
                $called = true;
                $this->assertEquals(['foo' => 'bar'], $workPlan);
                $this->assertInstanceOf(
                    ChefInterface::class,
                    $chef->continue(
                        ['foo' => 'bar2']
                    )
                );

                return $bowl;
            });

        $bowl2 = $this->createMock(BowlInterface::class);
        $bowl2->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($chefPassed, $workPlan) use ($chef, &$called, $bowl2): MockObject {
                $this->assertEquals(['foo' => 'bar2'], $workPlan);

                return $bowl2;
            });

        $chef->followSteps([$bowl, $bowl2]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );

        $this->assertTrue($called);
    }

    public function testExceptionWithoutExceptionBowlDefined(): void
    {
        $this->expectException(RuntimeException::class);
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function (): never {
                throw new RuntimeException('fooBar');
            });

        $bowl2 = $this->createMock(BowlInterface::class);
        $bowl2->expects($this->never())
            ->method('execute');

        $chef->followSteps([$bowl, $bowl2]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );
    }

    public function testExceptionWithExceptionBowlDefined(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function () use (&$called): never {
                throw new RuntimeException('fooBar');
            });

        $errorBowl = $this->createMock(BowlInterface::class);
        $errorBowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($chef, array &$workPlan) use (&$called, $errorBowl): MockObject {
                $this->assertInstanceOf(RuntimeException::class, $workPlan['exception']);
                $called = true;

                return $errorBowl;
            });

        $bowl2 = $this->createMock(BowlInterface::class);
        $bowl2->expects($this->never())
            ->method('execute');

        $chef->followSteps([$bowl, $bowl2], $errorBowl);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );

        $this->assertTrue($called);
    }

    public function testExceptionWithSeveralExceptionBowlDefined(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl1 = $this->createMock(BowlInterface::class);
        $bowl1->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function () use (&$called): never {
                throw new RuntimeException('fooBar');
            });

        $errorBowl1 = $this->createMock(BowlInterface::class);
        $errorBowl1->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($chef, array &$workPlan) use (&$called, $errorBowl1): MockObject {
                $this->assertInstanceOf(RuntimeException::class, $workPlan['exception']);
                $called = true;

                return $errorBowl1;
            });

        $errorBowl2 = $this->createMock(BowlInterface::class);
        $errorBowl2->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($chef, array &$workPlan) use (&$called, $errorBowl2): MockObject {
                $this->assertInstanceOf(RuntimeException::class, $workPlan['exception']);
                $called = true;

                return $errorBowl2;
            });

        $bowl2 = $this->createMock(BowlInterface::class);
        $bowl2->expects($this->never())
            ->method('execute');

        $chef->followSteps([$bowl1, $bowl2], [$errorBowl1, $errorBowl2]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );

        $this->assertTrue($called);
    }

    public function testContinueNextStep(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($chefPassed, $workPlan) use ($chef, &$called, $bowl): MockObject {
                $called = true;
                $this->assertEquals(['foo' => 'bar'], $workPlan);
                $this->assertInstanceOf(
                    ChefInterface::class,
                    $chef->continue(
                        ['foo' => 'bar2'],
                        'bowl3'
                    )
                );

                return $bowl;
            });

        $bowl2 = $this->createMock(BowlInterface::class);
        $bowl2->expects($this->never())
            ->method('execute');

        $bowl3 = $this->createMock(BowlInterface::class);
        $bowl3->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function ($chefPassed, $workPlan) use ($chef, &$called, $bowl3): MockObject {
                $this->assertEquals(['foo' => 'bar2'], $workPlan);

                return $bowl3;
            });

        $chef->followSteps(['bowl' => $bowl, 'bowl2' => $bowl2, 'bowl3' => $bowl3]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );

        $this->assertTrue($called);
    }

    public function testFinish(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl): MockObject {
                $called = true;
                $this->assertInstanceOf(
                    ChefInterface::class,
                    $chef->finish(
                        ['foo' => 'bar']
                    )
                );

                return $bowl;
            });

        $chef->followSteps([$bowl]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );

        $this->assertTrue($called);
    }

    public function testInterruptCooking(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl): MockObject {
                $called = true;
                $this->assertInstanceOf(
                    ChefInterface::class,
                    $chef->interruptCooking()
                );

                return $bowl;
            });

        $chef->followSteps([$bowl]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );

        $this->assertTrue($called);
    }

    public function testStopErrorReporting(): void
    {
        $chef = $this->buildChef();
        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->stopErrorReporting()
        );
    }

    public function testErrorWithNoErrorCatcher(): void
    {

        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl): MockObject {
                $called = true;
                $this->assertInstanceOf(
                    ChefInterface::class,
                    $chef->error(
                        new Exception('foo')
                    )
                );

                return $bowl;
            });

        $chef->followSteps([$bowl]);

        $this->expectException(Exception::class);
        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );

        $this->assertTrue($called);
    }

    public function testErrorWithCatcher(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl): MockObject {
                $called = true;
                $this->assertInstanceOf(
                    ChefInterface::class,
                    $chef->error(
                        new Exception('foo')
                    )
                );

                return $bowl;
            });

        $errorBowl = $this->createMock(BowlInterface::class);
        $errorBowl->expects($this->once())
            ->method('execute')
            ->willReturnSelf();

        $chef->followSteps([$bowl], [$errorBowl]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );

        $this->assertTrue($called);
    }

    public function testErrorWithBadThrowable(): void
    {
        $this->expectException(TypeError::class);

        $chef = $this->buildChef();
        $chef->error(new stdClass());
    }

    public function testExceptionOnProcessWithBadArray(): void
    {
        $this->expectException(TypeError::class);
        $this->buildChef()->missing(new stdClass());
    }

    public function testProcess(): void
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        $this->assertInstanceOf(
            ChefInterface::class,
            $chef->process(
                ['foo' => 'bar']
            )
        );
    }

    public function testExceptionProcessWithMissingIngredient(): void
    {
        $this->expectException(RuntimeException::class);
        $chef = $this->buildChef();
        $ingredient = $this->createMock(IngredientInterface::class);

        $recipe = $this->createMock(RecipeInterface::class);
        $recipe->expects($this->once())
            ->method('train')
            ->willReturnSelf();

        $recipe->expects($this->once())
            ->method('prepare')
            ->willReturnCallback(function ($workPlan, ChefInterface $chef) use ($recipe, $ingredient): MockObject {
                $this->assertInstanceOf(
                    ChefInterface::class,
                    $chef->missing($ingredient, 'Error')
                );

                return $recipe;
            });

        $chef->read($recipe);
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        $chef->process(['foo' => 'bar']);
    }

    public function testClone()
    {
        $chef = $this->buildChef();
        $chef2 = clone $chef;

        $this->assertNotSame($chef, $chef2);

        $chefSupervised = $this->buildChef($this->createMock(CookingSupervisorInterface::class));
        $chefSupervised2 = clone $chefSupervised;

        $this->assertNotSame($chef, $chef2);
    }
}
