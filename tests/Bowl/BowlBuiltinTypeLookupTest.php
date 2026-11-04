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

namespace Teknoo\Tests\Recipe\Bowl;

use BadMethodCallException;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;

/**
 * Non regression tests about the lookup of a parameter from the name of its type in the workplan: only a class or an
 * interface is a key of the workplan. An ingredient named as a builtin type (`mixed`, `int`, `string`...), for example
 * given by an end user, must never reach a parameter of this type.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
final class BowlBuiltinTypeLookupTest extends TestCase
{
    public function testAnIngredientNamedAsABuiltinTypeIsNotUsedForAParameterOfThisType(): void
    {
        $seen = null;

        $bowl = new Bowl(
            static function (
                mixed $mixedValue = 'default mixed',
                int $intValue = 1,
                ?int $nullableIntValue = null,
                string $stringValue = 'default string',
                float $floatValue = 1.5,
                bool $boolValue = false,
                array $arrayValue = [],
                iterable $iterableValue = [],
            ) use (&$seen): void {
                $seen = [
                    'mixed' => $mixedValue,
                    'int' => $intValue,
                    'nullableInt' => $nullableIntValue,
                    'string' => $stringValue,
                    'float' => $floatValue,
                    'bool' => $boolValue,
                    'array' => $arrayValue,
                    'iterable' => $iterableValue,
                ];
            },
            [],
            'builtin',
        );

        $workPlan = [
            'mixed' => ['injected' => true],
            'int' => 418,
            'string' => 'injected',
            'float' => 4.18,
            'bool' => true,
            'array' => ['injected'],
            'iterable' => ['injected'],
        ];

        $this->assertInstanceOf(
            BowlInterface::class,
            $bowl->execute($this->createStub(ChefInterface::class), $workPlan),
        );

        $this->assertSame(
            [
                'mixed' => 'default mixed',
                'int' => 1,
                'nullableInt' => null,
                'string' => 'default string',
                'float' => 1.5,
                'bool' => false,
                'array' => [],
                'iterable' => [],
            ],
            $seen,
        );
    }

    public function testAnIngredientNamedAsTheParameterIsStillUsed(): void
    {
        $seen = null;

        $bowl = new Bowl(
            static function (mixed $mixedValue = 'default', int $intValue = 1) use (&$seen): void {
                $seen = [$mixedValue, $intValue];
            },
            [],
            'builtin',
        );

        $workPlan = ['mixed' => 'not this one', 'mixedValue' => 'by name', 'intValue' => 42, 'int' => 418];
        $bowl->execute($this->createStub(ChefInterface::class), $workPlan);

        $this->assertSame(['by name', 42], $seen);
    }

    public function testAnIngredientNamedAsTheClassOfTheParameterIsStillUsed(): void
    {
        $date = new DateTime('2026-11-04');
        $seen = null;

        $bowl = new Bowl(
            static function (DateTimeInterface $date) use (&$seen): void {
                $seen = $date;
            },
            [],
            'builtin',
        );

        $workPlan = [DateTimeInterface::class => $date];
        $bowl->execute($this->createStub(ChefInterface::class), $workPlan);

        $this->assertSame($date, $seen);
    }

    public function testAMandatoryParameterOfABuiltinTypeIsMissingWithOnlyAnIngredientNamedAsItsType(): void
    {
        $bowl = new Bowl(
            static function (int $intValue): void {
            },
            [],
            'builtin',
        );

        $workPlan = ['int' => 418];

        $this->expectException(BadMethodCallException::class);
        $bowl->execute($this->createStub(ChefInterface::class), $workPlan);
    }
}
