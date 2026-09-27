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

use DateTime;
use DateTimeInterface;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use Teknoo\Tests\Recipe\Support\BackedEnumExample;

/**
 * Regression tests about the type checking performed by an ingredient.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Ingredient::class)]
final class IngredientTypeCheckTest extends TestCase
{
    /**
     * @param array<string, mixed> $expectedWorkPlan
     */
    private function chefExpectingIngredient(array $expectedWorkPlan): ChefInterface&MockObject
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects($this->never())->method('missing');
        $chef->expects($this->once())
            ->method('updateWorkPlan')
            ->with($expectedWorkPlan)
            ->willReturnSelf();

        return $chef;
    }

    private function chefExpectingMissing(string $messagePart): ChefInterface&MockObject
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects($this->never())->method('updateWorkPlan');
        $chef->expects($this->once())
            ->method('missing')
            ->with(
                $this->isInstanceOf(IngredientInterface::class),
                $this->stringContains($messagePart),
            )
            ->willReturnSelf();

        return $chef;
    }

    public function testTypeNamedLikeATwoArgumentsIsFunctionDoesNotCrash(): void
    {
        $workPlan = ['foo' => 'bar'];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient('a', 'foo'))->prepare($workPlan, $this->chefExpectingIngredient(['foo' => 'bar'])),
        );
    }

    public function testScalarTypeIsStillChecked(): void
    {
        $workPlan = ['foo' => 'bar'];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient('int', 'foo'))->prepare($workPlan, $this->chefExpectingMissing('must be a int')),
        );
    }

    public function testCountableTypeStillAcceptsArrays(): void
    {
        $workPlan = ['foo' => [1, 2]];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient('countable', 'foo'))->prepare($workPlan, $this->chefExpectingIngredient(['foo' => [1, 2]])),
        );
    }

    public function testUnnamedScalarIngredientIsStillRefused(): void
    {
        $this->expectException(LogicException::class);
        new Ingredient('string');
    }

    public function testUnnamedObjectIngredientIsStillAllowed(): void
    {
        $this->assertInstanceOf(IngredientInterface::class, new Ingredient('object'));
    }

    public function testOptionalScalarIngredientAbsentIsAcceptedAsNull(): void
    {
        $workPlan = ['foo' => 'bar'];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient('string', 'opt', mandatory: false))
                ->prepare($workPlan, $this->chefExpectingIngredient(['opt' => null])),
        );
    }

    public function testOptionalScalarIngredientPresentIsStillChecked(): void
    {
        $workPlan = ['opt' => 123];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient('string', 'opt', mandatory: false))
                ->prepare($workPlan, $this->chefExpectingMissing('must be a string')),
        );
    }

    public function testMandatoryScalarIngredientAbsentIsStillMissing(): void
    {
        $workPlan = ['foo' => 'bar'];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient('string', 'opt'))
                ->prepare($workPlan, $this->chefExpectingMissing('Missing the ingredient opt')),
        );
    }

    public function testInterfaceTypedIngredientAcceptsImplementation(): void
    {
        $date = new DateTime('2020-01-01');
        $workPlan = ['date' => $date];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient(DateTimeInterface::class, 'date'))
                ->prepare($workPlan, $this->chefExpectingIngredient(['date' => $date])),
        );
    }

    public function testInterfaceTypedIngredientRejectsOtherObject(): void
    {
        $workPlan = ['date' => new \stdClass()];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient(DateTimeInterface::class, 'date'))
                ->prepare($workPlan, $this->chefExpectingMissing('must implement DateTimeInterface')),
        );
    }

    public function testInterfaceTypedIngredientRejectsString(): void
    {
        $workPlan = ['date' => 'not a date'];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient(DateTimeInterface::class, 'date'))
                ->prepare($workPlan, $this->chefExpectingMissing('must implement DateTimeInterface')),
        );
    }

    public function testClassTypedIngredientRejectsInteger(): void
    {
        $workPlan = ['date' => 123];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient(DateTime::class, 'date'))
                ->prepare($workPlan, $this->chefExpectingMissing('must implement DateTime')),
        );
    }

    public function testClassTypedIngredientRejectsArray(): void
    {
        $workPlan = ['date' => ['2020-01-01']];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient(DateTime::class, 'date'))
                ->prepare($workPlan, $this->chefExpectingMissing('must implement DateTime')),
        );
    }

    public function testClassNameStringIsStillAccepted(): void
    {
        $workPlan = ['date' => DateTime::class];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient(DateTimeInterface::class, 'date'))
                ->prepare($workPlan, $this->chefExpectingIngredient(['date' => DateTime::class])),
        );
    }

    public function testNormalizerStillReceivesNonObjectValues(): void
    {
        $workPlan = ['date' => ['value' => '2020-01-01']];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient(
                DateTime::class,
                'date',
                normalizeCallback: static fn (array $value): DateTime => new DateTime($value['value']),
            ))->prepare($workPlan, $this->chefExpectingIngredient(['date' => new DateTime('2020-01-01')])),
        );
    }

    public function testNormalizerStillReceivesOnlyObjectsOfTheRequiredType(): void
    {
        $workPlan = ['date' => new \stdClass()];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient(
                DateTime::class,
                'date',
                normalizeCallback: static fn (DateTime $value): DateTime => $value,
            ))->prepare($workPlan, $this->chefExpectingMissing('must implement DateTime')),
        );
    }

    public function testBackedEnumStillNormalizesScalar(): void
    {
        $workPlan = ['enum' => 'val2'];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient(BackedEnumExample::class, 'enum'))
                ->prepare($workPlan, $this->chefExpectingIngredient(['enum' => BackedEnumExample::VAL2])),
        );
    }

    public function testOptionalObjectIngredientAbsentIsAcceptedAsNull(): void
    {
        $workPlan = [];

        $this->assertInstanceOf(
            IngredientInterface::class,
            (new Ingredient(DateTime::class, 'date', mandatory: false))
                ->prepare($workPlan, $this->chefExpectingIngredient(['date' => null])),
        );
    }
}
