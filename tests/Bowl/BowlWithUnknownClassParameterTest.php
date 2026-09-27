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
use Countable;
use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Tests\Recipe\Support\UnknownClassForBowlTest;
use Teknoo\Tests\Recipe\Transformable;

/**
 * Non regression tests about the resolution of parameters from their type (instanceof based).
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
final class BowlWithUnknownClassParameterTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $log = [];

    public function testUnknownClassTypeHintReportsMissingParameter(): void
    {
        //The class in the type hint does not exist, it is never resolved by PHP before the reflection
        $bowl = new Bowl(
            static function (UnknownClassForBowlTest $value): void {
            },
            [],
            'unknown',
        );

        $workPlan = ['foo' => new DateTime()];

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Missing the parameter value');
        $bowl->execute($this->createStub(ChefInterface::class), $workPlan);
    }

    public function testUnionAndInterfaceParametersStillResolve(): void
    {
        $bowl = new Bowl(
            function (DateTimeInterface|Countable $value): void {
                $this->log[] = $value::class;
            },
            [],
            'union',
        );

        $workPlan = ['foo' => new DateTime()];
        $this->assertInstanceOf(
            BowlInterface::class,
            $bowl->execute($this->createStub(ChefInterface::class), $workPlan),
        );

        $this->assertEquals([DateTime::class], $this->log);
    }

    public function testTransformableInstanceStillResolvesFromTheTransformClass(): void
    {
        $bowl = new Bowl(
            function (#[Transform(Transformable::class)] DateTime $value): void {
                $this->log[] = $value->format('Y');
            },
            [],
            'transform',
        );

        $workPlan = ['foo' => new Transformable(new DateTime('2021-01-01'))];
        $this->assertInstanceOf(
            BowlInterface::class,
            $bowl->execute($this->createStub(ChefInterface::class), $workPlan),
        );

        $this->assertEquals(['2021'], $this->log);
    }
}
