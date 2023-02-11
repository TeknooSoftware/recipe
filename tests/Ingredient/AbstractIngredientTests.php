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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Ingredient;

use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\IngredientBagInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractIngredientTests extends TestCase
{
    /**
     * @return IngredientInterface
     */
    abstract public function buildIngredient(): IngredientInterface;

    abstract public function getWorkPlanValid(): array;

    abstract public function getWorkPlanInvalidMissing(): array;

    abstract public function getWorkPlanInvalidNotInstanceOf(): array;

    abstract public function getWorkPlanInjected(): array;

    public function testExceptionOnPrepareWhenWorkPlanIsNotAnArray()
    {
        $this->expectException(\TypeError::class);
        $this->buildIngredient()->prepare(new \stdClass(), $this->createMock(RecipeInterface::class));
    }

    public function testExceptionOnPrepareWhenWorkPlanIsNotPromise()
    {
        $this->expectException(\TypeError::class);
        $this->buildIngredient()->prepare([], new \stdClass());
    }

    public function testPrepareWithValidPlan()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects(self::never())
            ->method('missing');

        $chef->expects(self::once())
            ->method('updateWorkPlan')
            ->with($this->getWorkPlanInjected())
            ->willReturnSelf();

        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $this->getWorkPlanValid(),
                $chef
            )
        );
    }

    public function testPrepareWithValidPlanWithBag()
    {
        $chef = $this->createMock(ChefInterface::class);
        $bag = $this->createMock(IngredientBagInterface::class);

        $chef->expects(self::never())
            ->method('missing');

        $chef->expects(self::never())
            ->method('updateWorkPlan')
            ->with($this->getWorkPlanInjected())
            ->willReturnSelf();

        $bag->expects(self::never())
            ->method('updateWorkPlan')
            ->with($this->getWorkPlanInjected())
            ->willReturnSelf();

        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $this->getWorkPlanValid(),
                $chef,
                $bag
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotPresent()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects(self::once())
            ->method('missing');

        $chef->expects(self::never())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $this->getWorkPlanInvalidMissing(),
                $chef
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotOfTheRequiredClass()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects(self::once())
            ->method('missing');

        $chef->expects(self::never())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $this->getWorkPlanInvalidNotInstanceOf(),
                $chef
            )
        );
    }
}
