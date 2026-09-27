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

use ArrayObject;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Tests\Recipe\Support\SelfParameterUserA;
use Teknoo\Tests\Recipe\Support\SelfParameterUserB;

/**
 * Regression tests about the key of the static cache of callables' parameters.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
final class BowlParameterCacheKeyTest extends TestCase
{
    public function testTwoBowlsWrappingInternalFunctionsDoNotShareParameters(): void
    {
        $chef = $this->createStub(ChefInterface::class);
        $date = new DateTime('2020-01-01 00:00:00');
        $workPlan = ['date' => $date, 'modifier' => '+1 day', 'hour' => 10, 'minute' => 30];

        //date_modify(DateTime $object, string $modifier) and date_time_set(DateTime $object, int $hour, int $minute...)
        //are internal functions altering their first argument
        $modify = new Bowl('date_modify', ['object' => 'date'], 'modify');
        $this->assertInstanceOf(BowlInterface::class, $modify->execute($chef, $workPlan));
        $this->assertEquals('2020-01-02 00:00:00', $date->format('Y-m-d H:i:s'));

        $timeSet = new Bowl('date_time_set', ['object' => 'date'], 'timeSet');
        $this->assertInstanceOf(BowlInterface::class, $timeSet->execute($chef, $workPlan));
        $this->assertEquals('2020-01-02 10:30:00', $date->format('Y-m-d H:i:s'));
    }

    public function testTwoBowlsWrappingInternalMethodsDoNotShareParameters(): void
    {
        $chef = $this->createStub(ChefInterface::class);
        $list = new ArrayObject();
        $workPlan = ['value' => 'ab', 'key' => 'k'];

        $append = new Bowl([$list, 'append'], [], 'append');
        $this->assertInstanceOf(BowlInterface::class, $append->execute($chef, $workPlan));
        $this->assertEquals(['ab'], $list->getArrayCopy());

        $set = new Bowl([$list, 'offsetSet'], [], 'set');
        $this->assertInstanceOf(BowlInterface::class, $set->execute($chef, $workPlan));
        $this->assertEquals(['ab', 'k' => 'ab'], $list->getArrayCopy());
    }

    public function testTraitMethodWithSelfParameterResolvesPerUsingClass(): void
    {
        $chef = $this->createStub(ChefInterface::class);

        $a = new SelfParameterUserA();
        $workPlan = ['instance' => $a];
        (new Bowl([$a, 'withSelf'], [], 'a'))->execute($chef, $workPlan);
        $this->assertSame($a, $a->seen);

        $b = new SelfParameterUserB();
        $workPlan = ['instance' => $b];
        (new Bowl([$b, 'withSelf'], [], 'b'))->execute($chef, $workPlan);
        $this->assertSame($b, $b->seen);
    }

    public function testInternalMethodClosuresOfDifferentClassesDoNotCollide(): void
    {
        $chef = $this->createStub(ChefInterface::class);
        $date = new DateTime('2020-01-01');
        $list = new ArrayObject([1, 2]);
        $workPlan = ['modifier' => '+1 month', 'value' => 3];

        $modify = new Bowl($date->modify(...), [], 'modify');
        $this->assertInstanceOf(BowlInterface::class, $modify->execute($chef, $workPlan));
        $this->assertEquals('2020-02-01', $date->format('Y-m-d'));

        $append = new Bowl($list->append(...), [], 'append');
        $this->assertInstanceOf(BowlInterface::class, $append->execute($chef, $workPlan));
        $this->assertEquals([1, 2, 3], $list->getArrayCopy());
    }
}
