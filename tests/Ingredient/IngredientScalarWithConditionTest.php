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
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Ingredient;

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Ingredient\IngredientBagInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Recipe\Ingredient\IngredientWithCondition;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Ingredient::class)]
#[CoversClass(IngredientWithCondition::class)]
final class IngredientScalarWithConditionTest extends AbstractIngredientTests
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
    public function testPrepareWithValidPlanAndCondition(): void
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
    public function testPrepareWithValidPlanWithBagAndCondition(): void
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
    public function testPrepareWithInvalidPlanTheIngredientIsNotPresentAndCondition(): void
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
    public function testPrepareWithInvalidPlanTheIngredientIsNotOfTheRequiredClassAndCondition(): void
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
    public function testPrepareWithValidPlanWithoutNameUseType(): void
    {
        $this->expectException(LogicException::class);
        $this->buildIngredientWithoutName();
    }
    public function testPrepareWithInvalidPlanTheIngredientIsNotPresentNameIsNullAndValueIsPresentUnderAnotherName(): void
    {
        $this->expectException(LogicException::class);
        $this->buildIngredientWithoutName();
    }
}
