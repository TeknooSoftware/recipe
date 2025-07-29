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

namespace Teknoo\Tests\Recipe\Ingredient;

use TypeError;
use stdClass;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\IngredientBagInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractIngredientTests extends TestCase
{
    abstract public function buildIngredient(bool $mandatory = true, mixed $default = null): IngredientInterface;

    abstract public function buildIngredientWithoutName(
        bool $mandatory = true,
        mixed $default = null,
    ): IngredientInterface;

    abstract public function getWorkPlanValid(): array;

    abstract public function getWorkPlanKeyUnderAnotherName(): array;

    abstract public function getWorkPlanInvalidMissing(): array;

    abstract public function getWorkPlanInvalidNotInstanceOf(): array;

    abstract public function getWorkPlanInjected(): array;

    abstract public function getWorkPlanInjectedWithoutName(): array;

    abstract public function getDefaultValue(): mixed;

    public function testExceptionOnPrepareWhenWorkPlanIsNotAnArray(): void
    {
        $this->expectException(TypeError::class);
        $s = new stdClass();
        $this->buildIngredient()->prepare($s, $this->createMock(RecipeInterface::class));
    }

    public function testExceptionOnPrepareWhenWorkPlanIsNotPromise(): void
    {
        $this->expectException(TypeError::class);
        $a = [];
        $this->buildIngredient()->prepare($a, new stdClass());
    }

    public function testPrepareWithValidPlan(): void
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects($this->never())
            ->method('missing');

        $chef->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->getWorkPlanInjected())
            ->willReturnSelf();

        $a = $this->getWorkPlanValid();
        $this->assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithValidPlanWithoutNameUseType(): void
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects($this->never())
            ->method('missing');

        $chef->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->getWorkPlanInjectedWithoutName())
            ->willReturnSelf();

        $a = $this->getWorkPlanValid();
        $this->assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredientWithoutName()->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithValidPlanWithBag(): void
    {
        $chef = $this->createMock(ChefInterface::class);
        $bag = $this->createMock(IngredientBagInterface::class);

        $chef->expects($this->never())
            ->method('missing');

        $chef->expects($this->never())
            ->method('updateWorkPlan')
            ->with($this->getWorkPlanInjected())
            ->willReturnSelf();

        $bag->expects($this->never())
            ->method('updateWorkPlan')
            ->with($this->getWorkPlanInjected())
            ->willReturnSelf();

        $a = $this->getWorkPlanValid();
        $this->assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef,
                $bag
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotPresentNameIsSetAndValueMissing(): void
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects($this->once())
            ->method('missing');

        $chef->expects($this->never())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        $a = $this->getWorkPlanInvalidMissing();
        $this->assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotPresentNameIsSetAndValueIsPresentUnderAnotherName(): void
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects($this->once())
            ->method('missing');

        $chef->expects($this->never())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        $a = $this->getWorkPlanKeyUnderAnotherName();
        $this->assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotPresentNameIsNullAndValueIsPresentUnderAnotherName(): void
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects($this->never())
            ->method('missing');

        $chef->expects($this->once())
            ->method('updateWorkPlan')
            ->with($this->getWorkPlanInjectedWithoutName())
            ->willReturnSelf();

        $a = $this->getWorkPlanKeyUnderAnotherName();
        $this->assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredientWithoutName()->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotPresentButNotMandatory(): void
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects($this->never())
            ->method('missing');

        $chef->expects($this->once())
            ->method('updateWorkPlan')
            ->with(['IngName' => $this->getDefaultValue()])
            ->willReturnSelf();

        $a = $this->getWorkPlanInvalidMissing();
        $this->assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient(
                mandatory: false,
                default: $this->getDefaultValue(),
            )->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotOfTheRequiredClass(): void
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects($this->once())
            ->method('missing');

        $chef->expects($this->never())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        $a = $this->getWorkPlanInvalidNotInstanceOf();
        $this->assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef
            )
        );
    }
}
