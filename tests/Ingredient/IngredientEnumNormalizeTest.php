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
 * @covers \Teknoo\Recipe\Ingredient\Ingredient
 */
class IngredientEnumNormalizeTest extends AbstractIngredientTests
{
    /**
     * @inheritDoc
     */
    public function buildIngredient(
        $requiredType = BackedEnumExample::class,
        $name = 'ing_name',
        $normalize = 'IngName',
        callable $callback = null,
    ): IngredientInterface {
        return new Ingredient(
            requiredType: $requiredType,
            name: $name,
            normalizedName: $normalize,
            normalizeCallback: $callback,
        );
    }

    /**
     * @inheritDoc
     */
    public function getWorkPlanValid(): array
    {
        return [
            'ing_name' => 'val1'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getWorkPlanInvalidMissing(): array
    {
        return [
            'foo' => 'fooBar'
        ];
    }


    /**
     * @inheritDoc
     */
    public function getWorkPlanInvalidNotInstanceOf(): array
    {
        return [
            'ing_name' => 'fooBar'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getWorkPlanInjected(): array
    {
        return [
            'IngName' => BackedEnumExample::VAL1
        ];
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotANonBackedEnum()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects(self::once())
            ->method('missing');

        $chef->expects(self::never())
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
}
