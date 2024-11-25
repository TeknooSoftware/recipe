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

use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Tests\Recipe\Support\BackedEnumExample;
use Teknoo\Tests\Recipe\Support\EnumExample;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Ingredient::class)]
final class IngredientEnumNormalizeTest extends AbstractIngredientTests
{
    public function buildIngredient(
        bool $mandatory = true,
        mixed $default = null,
        $requiredType = BackedEnumExample::class,
        $name = 'ing_name',
        $normalize = 'IngName',
        ?callable $callback = null,
    ): IngredientInterface {
        return new Ingredient(
            requiredType: $requiredType,
            name: $name,
            normalizedName: $normalize,
            normalizeCallback: $callback,
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
            'ing_name' => BackedEnumExample::VAL1,
        ];
    }
    public function getWorkPlanKeyUnderAnotherName(): array
    {
        return [
            'ing_name_2' => BackedEnumExample::VAL1,
        ];
    }
    public function getWorkPlanInvalidMissing(): array
    {
        return [
            'foo' => 'fooBar'
        ];
    }
    public function getWorkPlanInvalidNotInstanceOf(): array
    {
        return [
            'ing_name' => 'fooBar'
        ];
    }
    public function getWorkPlanInjected(): array
    {
        return [
            'IngName' => BackedEnumExample::VAL1
        ];
    }
    public function getWorkPlanInjectedWithoutName(): array
    {
        return [
            'IngName' => BackedEnumExample::VAL1
        ];
    }
    public function getDefaultValue(): mixed
    {
        return 'val1';
    }
    public function testPrepareWithInvalidPlanTheIngredientIsNotANonBackedEnum(): void
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects($this->once())
            ->method('missing');

        $chef->expects($this->never())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        $a = [
            'ing_name' => 'val1'
        ];

        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient(
                requiredType: EnumExample::class,
            )->prepare(
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
            ->with(['IngName' => BackedEnumExample::VAL1])
            ->willReturnSelf();

        $a = $this->getWorkPlanInvalidMissing();
        self::assertInstanceOf(
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
}
