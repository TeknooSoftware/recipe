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

use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Tests\Recipe\Transformable;

/**
 * Non regression tests about the cache of the Transform attribute of bowls' parameters.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
final class BowlTransformCacheTest extends TestCase
{
    public static function upper(string $value): string
    {
        return strtoupper($value);
    }

    public function testTransformIsAppliedOnEveryExecution(): void
    {
        $chef = $this->createStub(ChefInterface::class);
        $log = [];

        $bowl = new Bowl(
            static function (
                #[Transform(transformer: [BowlTransformCacheTest::class, 'upper'])] string $value,
            ) use (&$log): void {
                $log[] = $value;
            },
            [],
            'upper',
        );

        $workPlan = ['value' => 'ab'];
        $this->assertInstanceOf(BowlInterface::class, $bowl->execute($chef, $workPlan));
        $workPlan = ['value' => 'cd'];
        $this->assertInstanceOf(BowlInterface::class, $bowl->execute($chef, $workPlan));

        $this->assertEquals(['AB', 'CD'], $log);
    }

    public function testTransformCacheIsPerParameter(): void
    {
        $chef = $this->createStub(ChefInterface::class);
        $log = [];

        $withTransform = new Bowl(
            static function (#[Transform(Transformable::class)] DateTime $value) use (&$log): void {
                $log[] = $value->format('Y');
            },
            [],
            'with',
        );
        $withoutTransform = new Bowl(
            static function (Transformable $value) use (&$log): void {
                $log[] = $value::class;
            },
            [],
            'without',
        );

        $workPlan = ['value' => new Transformable(new DateTime('2020-01-01'))];
        $this->assertInstanceOf(BowlInterface::class, $withTransform->execute($chef, $workPlan));
        $this->assertInstanceOf(BowlInterface::class, $withoutTransform->execute($chef, $workPlan));
        $this->assertInstanceOf(BowlInterface::class, $withTransform->execute($chef, $workPlan));

        $this->assertEquals(['2020', Transformable::class, '2020'], $log);
    }
}
