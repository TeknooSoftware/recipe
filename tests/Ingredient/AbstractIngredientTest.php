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

namespace Teknoo\Tests\Recipe\Ingredient;

use Teknoo\Recipe\Ingredient\IngredientInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\RecipeInterface;

abstract class AbstractIngredientTest extends TestCase
{
    /**
     * @return IngredientInterface
     */
    abstract public function buildIngredient(): IngredientInterface;

    /**
     * @return array
     */
    abstract public function getWorkPlanValid(): array;

    /**
     * @return array
     */
    abstract public function getWorkPlanInvalid(): array;

    /**
     * @return array
     */
    abstract public function getWorkPlanInjected(): array;

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnPrepareWhenWorkPlanIsNotAnArray()
    {
        $this->buildIngredient()->prepare(new \stdClass(), $this->createMock(RecipeInterface::class));
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnPrepareWhenWorkPlanIsNotPromise()
    {
        $this->buildIngredient()->prepare([], new \stdClass());
    }

    public function testPrepareWithValidPlan()
    {
        $recipe = $this->createMock(RecipeInterface::class);

        $recipe->expects(self::never())
            ->method('missing');

        $recipe->expects(self::once())
            ->method('prepareWorkPlan')
            ->with($this->getWorkPlanInjected())
            ->willReturnSelf();

        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $this->getWorkPlanValid(),
                $recipe
            )
        );
    }

    public function testPrepareWithInvalidPlan()
    {
        $recipe = $this->createMock(RecipeInterface::class);

        $recipe->expects(self::once())
            ->method('missing')
            ->willReturnSelf();

        $recipe->expects(self::never())
            ->method('prepareWorkPlan');

        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $this->getWorkPlanInvalid(),
                $recipe
            )
        );
    }
}