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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\AbstractDynamicBowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\ChefInterface;

/**
 * Non regression test : a dynamic bowl must resolve the parameters of the callable currently available in the
 * workplan, even if the callable changes between two executions.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AbstractDynamicBowl::class)]
#[CoversClass(DynamicBowl::class)]
final class DynamicBowlCallableSwitchTest extends TestCase
{
    public function testParametersFollowTheCurrentCallable(): void
    {
        $chef = $this->createStub(ChefInterface::class);
        $bowl = new DynamicBowl('toCall', true, [], 'dynamic');
        $log = [];

        $first = static function (string $a) use (&$log): void {
            $log[] = 'first:' . $a;
        };
        $second = static function (int $b, string $a) use (&$log): void {
            $log[] = 'second:' . $b . ':' . $a;
        };

        $workPlan = ['a' => 'foo', 'b' => 42, 'toCall' => $first];
        $this->assertInstanceOf(BowlInterface::class, $bowl->execute($chef, $workPlan));

        $workPlan['toCall'] = $second;
        $this->assertInstanceOf(BowlInterface::class, $bowl->execute($chef, $workPlan));

        $workPlan['toCall'] = $first;
        $this->assertInstanceOf(BowlInterface::class, $bowl->execute($chef, $workPlan));

        $this->assertEquals(['first:foo', 'second:42:foo', 'first:foo'], $log);
    }
}
