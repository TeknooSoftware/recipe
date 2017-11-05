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
        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->read(
                $this->createMock(RecipeInterface::class)
            )
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
        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->missing(
                $this->createMock(IngredientInterface::class),
                'fooBar'
            )
        );
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
        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->updateWorkPlan(
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
        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->continue(
                ['foo'=>'bar']
            )
        );
    }

    public function testFinish()
    {
        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->finish(
                ['foo'=>'bar']
            )
        );
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
        self::assertInstanceOf(
            ChefInterface::class,
            $this->buildChef()->process(
                ['foo'=>'bar']
            )
        );
    }
}