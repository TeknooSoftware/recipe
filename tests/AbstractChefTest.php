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

use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\RecipeInterface;

abstract class AbstractChefTest extends TestCase
{
    abstract public function buildChef(): ChefInterface;

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnReadWithBadRecipe()
    {
        $this->buildChef()->read(new \stdClass());
    }

    public function testRead()
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

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnMissingWithBadIngredient()
    {
        $this->buildChef()->missing(new \stdClass(), 'fooBar');
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnMissingWithBadMessage()
    {
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

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnUpdateWorkPlanWithBadArray()
    {
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

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnFollowStepsWithBadArray()
    {
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

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnContinueWithBadArray()
    {
        $this->buildChef()->missing(new \stdClass());
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

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnProcessWithBadArray()
    {
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

    /**
     * @expectedException \RuntimeException
     */
    public function testExceptionProcessWithMissingIngredient()
    {
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