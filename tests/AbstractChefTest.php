<?php

/**
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

namespace Teknoo\Tests\Recipe;

use RuntimeException;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\Ingredient\MergeableInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractChefTest extends TestCase
{
    abstract public function buildChef(): ChefInterface;

    public function testExceptionOnReadWithBadRecipe()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->read(new \stdClass());
    }

    public function testReadWithRecipe()
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $recipe->expects(self::once())
            ->method('train')
            ->willReturnSelf();

        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->read($recipe)
        );
    }

    public function testReadWithCookbook()
    {
        $recipe = $this->createMock(CookbookInterface::class);
        $recipe->expects(self::once())
            ->method('train')
            ->willReturnSelf();

        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->read($recipe)
        );
    }

    public function testReadWithBaseRecipe()
    {
        $recipe = $this->createMock(BaseRecipeInterface::class);
        $recipe->expects(self::once())
            ->method('train')
            ->willReturnSelf();

        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->read($recipe)
        );
    }

    public function testExceptionOnReserveAndBeginWithBadRecipe()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->reserveAndBegin(new \stdClass());
    }

    public function testReserveAndBeginAvailableOnCookingWithRecipe()
    {
        $mainRecipe = $this->createMock(RecipeInterface::class);
        $mainRecipe->expects(self::once())
            ->method('train')
            ->willReturnSelf();

        $subRecipe = $this->createMock(RecipeInterface::class);
        $subRecipe->expects(self::once())
            ->method('train')
            ->willReturnCallback(function (ChefInterface $chef) use ($subRecipe) {
                $chef->followSteps(['substep' => $this->createMock(BowlInterface::class)]);

                return $subRecipe;
            });

        $chef = $this->buildChef();
        self::assertInstanceOf(
            ChefInterface::class,
            $chef->read($mainRecipe)
        );

        $step = $this->createMock(BowlInterface::class);
        $step->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function (ChefInterface $chef) use ($step, $subRecipe) {
                self::assertInstanceOf(
                    ChefInterface::class,
                    $subchef = $chef->reserveAndBegin($subRecipe)
                );

                self::assertNotSame(
                    $chef,
                    $subchef
                );

                return $step;
            });

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->followSteps(['step' => $step])
        );

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );
    }


    public function testReserveAndBeginAvailableOnCookingWithBaseRecipe()
    {
        $mainRecipe = $this->createMock(RecipeInterface::class);
        $mainRecipe->expects(self::once())
            ->method('train')
            ->willReturnSelf();

        $subRecipe = $this->createMock(BaseRecipeInterface::class);
        $subRecipe->expects(self::once())
            ->method('train')
            ->willReturnCallback(function (ChefInterface $chef) use ($subRecipe) {
                $chef->followSteps(['substep' => $this->createMock(BowlInterface::class)]);

                return $subRecipe;
            });

        $chef = $this->buildChef();
        self::assertInstanceOf(
            ChefInterface::class,
            $chef->read($mainRecipe)
        );

        $step = $this->createMock(BowlInterface::class);
        $step->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function (ChefInterface $chef) use ($step, $subRecipe) {
                self::assertInstanceOf(
                    ChefInterface::class,
                    $subchef = $chef->reserveAndBegin($subRecipe)
                );

                self::assertNotSame(
                    $chef,
                    $subchef
                );

                return $step;
            });

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->followSteps(['step' => $step])
        );

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );
    }

    public function testReserveAndBeginAvailableOnCookingWithCookbook()
    {
        $mainRecipe = $this->createMock(RecipeInterface::class);
        $mainRecipe->expects(self::once())
            ->method('train')
            ->willReturnSelf();

        $subRecipe = $this->createMock(CookbookInterface::class);
        $subRecipe->expects(self::once())
            ->method('train')
            ->willReturnCallback(function (ChefInterface $chef) use ($subRecipe) {
                $chef->followSteps(['substep' => $this->createMock(BowlInterface::class)]);

                return $subRecipe;
            });

        $chef = $this->buildChef();
        self::assertInstanceOf(
            ChefInterface::class,
            $chef->read($mainRecipe)
        );

        $step = $this->createMock(BowlInterface::class);
        $step->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function (ChefInterface $chef) use ($step, $subRecipe) {
                self::assertInstanceOf(
                    ChefInterface::class,
                    $subchef = $chef->reserveAndBegin($subRecipe)
                );

                self::assertNotSame(
                    $chef,
                    $subchef
                );

                return $step;
            });

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->followSteps(['step' => $step])
        );

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );
    }

    public function testReserveAndBeginOnNonTrainedChefWithRecipe()
    {
        $this->expectException(\Throwable::class);
        $recipe = $this->createMock(RecipeInterface::class);
        $this->buildChef()->reserveAndBegin($recipe);
    }

    public function testReserveAndBeginOnNonTrainedChefWithCookbook()
    {
        $this->expectException(\Throwable::class);
        $recipe = $this->createMock(CookbookInterface::class);
        $this->buildChef()->reserveAndBegin($recipe);
    }

    public function testReserveAndBeginOnNonTrainedChefWithBaseRecipe()
    {
        $this->expectException(\Throwable::class);
        $recipe = $this->createMock(BaseRecipeInterface::class);
        $this->buildChef()->reserveAndBegin($recipe);
    }

    public function testExceptionOnMissingWithBadIngredient()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->missing(new \stdClass(), 'fooBar');
    }

    public function testExceptionOnMissingWithBadMessage()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->missing(
            $this->createMock(IngredientInterface::class),
            new \stdClass()
        );
    }

    public function testMissing()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl) {
                $called = true;
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->missing(
                        $this->createMock(IngredientInterface::class),
                        'fooBar'
                    )
                );

                return $bowl;
            });

        $chef->followSteps([$bowl]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );

        self::assertTrue($called);
    }

    public function testExceptionOnUpdateWorkPlanWithBadArray()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->missing(new \stdClass());
    }

    public function testUpdateWorkPlan()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->updateWorkPlan(
                ['foo'=>'bar']
            )
        );
    }

    public function testExceptionOnUpdateWorkPlanWithBadName()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->missing(new \stdClass(), $this->createMock(MergeableInterface::class));
    }

    public function testExceptionOnUpdateWorkPlanWithBadValue()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->missing('foo', new \stdClass());
    }

    public function testMerge()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([
            new Bowl(
                static function ($foo) {
                    self::assertEquals(9, $foo->value);
                },
                [],
            )
        ]);

        $c1 = new class implements MergeableInterface
        {
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

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->merge(
                'foo', $c1
            )
        );

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->merge(
                'foo', $c2
            )
        );

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->merge(
                'foo', $c3
            )
        );

        $chef->process([]);

        self::assertEquals(9, $c1->value);
    }

    public function testMergeWithNonMergeable()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->updateWorkPlan(
                ['foo' => new \stdClass()]
            )
        );

        $this->expectException(RuntimeException::class);
        $chef->merge(
            'foo', $this->createMock(MergeableInterface::class)
        );
    }

    public function testMergeWithNonMergeableAfterUpdate()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->updateWorkPlan(
                ['foo' => $this->createMock(MergeableInterface::class)]
            )
        );

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->updateWorkPlan(
                ['foo' => new \stdClass()]
            )
        );

        $this->expectException(RuntimeException::class);
        $chef->merge(
            'foo', $this->createMock(MergeableInterface::class)
        );
    }

    public function testExceptionOnCleanWorkPlanWithBadArray()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->missing(new \stdClass());
    }

    public function testCleanWorkPlan()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->cleanWorkPlan(
                'foo', 'bar'
            )
        );
    }

    public function testExceptionOnFollowStepsWithBadArray()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->missing(new \stdClass());
    }

    public function testFollowSteps()
    {
        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->followSteps(
                ['foo'=>'bar']
            )
        );
    }

    public function testFollowStepsWithErrorAsArray()
    {
        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->followSteps(
                ['foo'=>'bar'],
                ['foo2']
            )
        );
    }

    public function testFollowStepsWithErrorAsBowl()
    {
        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->followSteps(
                ['foo'=>'bar'],
                $this->createMock(BowlInterface::class)
            )
        );
    }

    public function testExceptionOnContinueWithBadArray()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->missing(new \stdClass());
    }

    public function testExceptionOnContinueWithBadNextStep()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->missing([], new \stdClass());
    }

    public function testContinue()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function ($chefPassed, $workPlan) use ($chef, &$called, $bowl) {
                $called = true;
                self::assertEquals(['foo'=>'bar'], $workPlan);
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->continue(
                        ['foo'=>'bar2']
                    )
                );

                return $bowl;
            });

        $bowl2 = $this->createMock(BowlInterface::class);
        $bowl2->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function ($chefPassed, $workPlan) use ($chef, &$called, $bowl2) {
                self::assertEquals(['foo'=>'bar2'], $workPlan);

                return $bowl2;
            });

        $chef->followSteps([$bowl, $bowl2]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );

        self::assertTrue($called);
    }

    public function testExceptionWithoutExceptionBowlDefined()
    {
        $this->expectException(RuntimeException::class);
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function () {
                throw new RuntimeException('fooBar');
            });

        $bowl2 = $this->createMock(BowlInterface::class);
        $bowl2->expects(self::never())
            ->method('execute');

        $chef->followSteps([$bowl, $bowl2]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );
    }

    public function testExceptionWithExceptionBowlDefined()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function () use (&$called) {
                throw new RuntimeException('fooBar');
            });

        $errorBowl = $this->createMock(BowlInterface::class);
        $errorBowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function ($chef, &$workPlan) use (&$called, $errorBowl) {
                self::assertInstanceOf(RuntimeException::class, $workPlan['exception']);
                $called = true;

                return $errorBowl;
            });

        $bowl2 = $this->createMock(BowlInterface::class);
        $bowl2->expects(self::never())
            ->method('execute');

        $chef->followSteps([$bowl, $bowl2], $errorBowl);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );

        self::assertTrue($called);
    }

    public function testExceptionWithSeveralExceptionBowlDefined()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl1 = $this->createMock(BowlInterface::class);
        $bowl1->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function () use (&$called) {
                throw new RuntimeException('fooBar');
            });

        $errorBowl1 = $this->createMock(BowlInterface::class);
        $errorBowl1->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function ($chef, &$workPlan) use (&$called, $errorBowl1) {
                self::assertInstanceOf(RuntimeException::class, $workPlan['exception']);
                $called = true;

                return $errorBowl1;
            });

        $errorBowl2 = $this->createMock(BowlInterface::class);
        $errorBowl2->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function ($chef, &$workPlan) use (&$called, $errorBowl2) {
                self::assertInstanceOf(RuntimeException::class, $workPlan['exception']);
                $called = true;

                return $errorBowl2;
            });

        $bowl2 = $this->createMock(BowlInterface::class);
        $bowl2->expects(self::never())
            ->method('execute');

        $chef->followSteps([$bowl1, $bowl2], [$errorBowl1, $errorBowl2]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );

        self::assertTrue($called);
    }

    public function testContinueNextStep()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function ($chefPassed, $workPlan) use ($chef, &$called, $bowl) {
                $called = true;
                self::assertEquals(['foo'=>'bar'], $workPlan);
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->continue(
                        ['foo'=>'bar2'],
                        'bowl3'
                    )
                );

                return $bowl;
            });

        $bowl2 = $this->createMock(BowlInterface::class);
        $bowl2->expects(self::never())
            ->method('execute');

        $bowl3 = $this->createMock(BowlInterface::class);
        $bowl3->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function ($chefPassed, $workPlan) use ($chef, &$called, $bowl3) {
                self::assertEquals(['foo'=>'bar2'], $workPlan);

                return $bowl3;
            });

        $chef->followSteps(['bowl' => $bowl, 'bowl2' => $bowl2, 'bowl3' => $bowl3]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );

        self::assertTrue($called);
    }

    public function testFinish()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl) {
                $called = true;
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->finish(
                        ['foo'=>'bar']
                    )
                );

                return $bowl;
            });

        $chef->followSteps([$bowl]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );

        self::assertTrue($called);
    }

    public function testInterruptCooking()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl) {
                $called = true;
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->interruptCooking()
                );

                return $bowl;
            });

        $chef->followSteps([$bowl]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );

        self::assertTrue($called);
    }

    public function testStopErrorReporting()
    {
        $chef = $this->buildChef();
        self::assertInstanceOf(
            ChefInterface::class,
            $chef->stopErrorReporting()
        );
    }

    public function testErrorWithNoErrorCatcher()
    {

        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl) {
                $called = true;
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->error(
                        new \Exception('foo')
                    )
                );

                return $bowl;
            });

        $chef->followSteps([$bowl]);

        $this->expectException(\Exception::class);
        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );

        self::assertTrue($called);
    }

    public function testErrorWithCatcher()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl) {
                $called = true;
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->error(
                        new \Exception('foo')
                    )
                );

                return $bowl;
            });

        $errorBowl = $this->createMock(BowlInterface::class);
        $errorBowl->expects(self::once())
            ->method('execute')
            ->willReturnSelf();

        $chef->followSteps([$bowl], [$errorBowl]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );

        self::assertTrue($called);
    }

    public function testErrorWithBadThrowable()
    {
        $this->expectException(\TypeError::class);

        $chef = $this->buildChef();
        $chef->error(new \stdClass());
    }

    public function testExceptionOnProcessWithBadArray()
    {
        $this->expectException(\TypeError::class);
        $this->buildChef()->missing(new \stdClass());
    }

    public function testProcess()
    {
        $chef = $this->buildChef();
        $chef->read($this->createMock(RecipeInterface::class));
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(
                ['foo'=>'bar']
            )
        );
    }

    public function testExceptionProcessWithMissingIngredient()
    {
        $this->expectException(RuntimeException::class);
        $chef = $this->buildChef();
        $ingredient = $this->createMock(IngredientInterface::class);

        $recipe = $this->createMock(RecipeInterface::class);
        $recipe->expects(self::once())
            ->method('train')
            ->willReturnSelf();

        $recipe->expects(self::once())
            ->method('prepare')
            ->willReturnCallback(function ($workPlan, ChefInterface $chef) use ($recipe, $ingredient) {
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->missing($ingredient, 'Error')
                );

                return $recipe;
            });

        $chef->read($recipe);
        $chef->followSteps([$this->createMock(BowlInterface::class)]);

        $chef->process(['foo'=>'bar']);
    }
}
