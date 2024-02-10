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
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Ingredient\IngredientInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Recipe\Ingredient\Ingredient
 */
class IngredientScalarTest extends AbstractIngredientTests
{
    public function buildIngredient(
        bool $mandatory = true,
        mixed $default = null,
        $requiredType = 'string',
        $name = 'IngName',
    ): IngredientInterface {
        return new Ingredient(
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

    public function getWorkPlanInvalidMissing(): array
    {
        return [
            'foo' => 'fooBar'
        ];
    }

    public function getWorkPlanInvalidNotInstanceOf(): array
    {
        return [
            'IngName' => 123
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

    public function testIngredientNonMandatoryWithDefaultValue()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects(self::never())
            ->method('missing');

        $chef->expects(self::once())
            ->method('updateWorkPlan')
            ->with(
                [
                    'foo' => [],
                ]
            )
            ->willReturnSelf();

        $a = [];

        $ingredient = new Ingredient(
            requiredType: 'array',
            name: 'foo',
            mandatory: false,
            default: [],
        );

        self::assertInstanceOf(
            IngredientInterface::class,
            $ingredient->prepare(
                $a,
                $chef
            )
        );
    }
}
