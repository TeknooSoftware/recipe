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

namespace Teknoo\Tests\Recipe\Ingredient;

use LogicException;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\IngredientBagInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\Ingredient\IngredientWithCondition;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Recipe\Ingredient\Ingredient
 * @covers \Teknoo\Recipe\Ingredient\IngredientWithCondition
 */
class IngredientScalarWithConditionTest extends AbstractIngredientTests
{
    public function buildIngredient(
        bool $mandatory = true,
        mixed $default = null,
        $requiredType = 'string',
        $name = 'IngName',
    ): IngredientInterface {
        $conditon = function (
            array $workplan,
            ChefInterface $chef,
            ?IngredientBagInterface $bag = null,
        ): bool {
            self::assertInstanceOf(ChefInterface::class, $chef);
            return empty($workplan['byPass']);
        };

        return new IngredientWithCondition(
            conditionCallback: $conditon,
            requiredType: $requiredType,
            name: $name,
            mandatory: $mandatory,
            default: $default,
        );
    }

    public function buildIngredientWithoutName(
        bool $mandatory = true,
        mixed $default = null,
    ): IngredientInterface {
        return $this->buildIngredient(
            mandatory: $mandatory,
            default: $default,
            name: null,
        );
    }

    public function getWorkPlanValid(): array
    {
        return [
            'IngName' => 'fooBar'
        ];
    }

    public function getWorkPlanKeyUnderAnotherName(): array
    {
        return [
            'IngName2' => 'fooBar'
        ];
    }

    public function getWorkPlanValidAndByPass(): array
    {
        return [
            'IngName' => 'fooBar',
            'byPass' => true,
        ];
    }

    public function getWorkPlanInvalidMissing(): array
    {
        return [
            'foo' => 'fooBar'
        ];
    }

    public function getWorkPlanInvalidMissingAndByPass(): array
    {
        return [
            'foo' => 'fooBar',
            'byPass' => true,
        ];
    }

    public function getWorkPlanInvalidNotInstanceOf(): array
    {
        return [
            'IngName' => 123
        ];
    }

    public function getWorkPlanInvalidNotInstanceOfAndByPass(): array
    {
        return [
            'IngName' => 123,
            'byPass' => true,
        ];
    }

    public function getWorkPlanInjected(): array
    {
        return [
            'IngName' => 'fooBar'
        ];
    }

    public function getWorkPlanInjectedWithoutName(): array
    {
        return $this->getWorkPlanInjected();
    }

    public function getDefaultValue(): mixed
    {
        return 'fooBar';
    }

    public function testPrepareWithValidPlanAndCondition()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects($this->never())
            ->method('missing');

        $chef->expects($this->never())
            ->method('updateWorkPlan');

        $a = $this->getWorkPlanValidAndByPass();
        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithValidPlanWithBagAndCondition()
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

        $a = $this->getWorkPlanValidAndByPass();
        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef,
                $bag
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotPresentAndCondition()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects($this->never())
            ->method('missing');

        $chef->expects($this->never())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        $a = $this->getWorkPlanInvalidMissingAndByPass();
        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotOfTheRequiredClassAndCondition()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects($this->never())
            ->method('missing');

        $chef->expects($this->never())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        $a = $this->getWorkPlanInvalidNotInstanceOfAndByPass();
        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithValidPlanWithoutNameUseType()
    {
        $this->expectException(LogicException::class);
        $this->buildIngredientWithoutName();
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotPresentNameIsNullAndValueIsPresentUnderAnotherName()
    {
        $this->expectException(LogicException::class);
        $this->buildIngredientWithoutName();
    }
}
