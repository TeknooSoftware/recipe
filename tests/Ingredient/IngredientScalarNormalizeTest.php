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
use Teknoo\Recipe\Ingredient\IngredientInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Ingredient::class)]
final class IngredientScalarNormalizeTest extends AbstractIngredientTests
{
    public function buildIngredient(
        bool $mandatory = true,
        mixed $default = null,
        $requiredType = 'numeric',
        $name = 'ing_name',
        $normalize = 'IngName',
        $callback = 'intval',
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
            'ing_name' => '123'
        ];
    }
    public function getWorkPlanKeyUnderAnotherName(): array
    {
        return [
            'ing_name_2' => '123'
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
            'IngName' => 123
        ];
    }
    public function getWorkPlanInjectedWithoutName(): array
    {
        return $this->getWorkPlanInjected();
    }
    public function getDefaultValue(): mixed
    {
        return 123;
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
