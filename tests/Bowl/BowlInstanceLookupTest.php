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

/**
 * Non regression tests about the lookup of a parameter from its type in the workplan.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
final class BowlInstanceLookupTest extends TestCase
{
    public function testFirstMatchingInstanceIsUsedAndWorkplanIsUntouched(): void
    {
        $first = new DateTime('2020-01-01');
        $second = new DateTime('2021-01-01');
        $seen = null;

        $bowl = new Bowl(
            static function (DateTime $date) use (&$seen): void {
                $seen = $date;
            },
            [],
            'lookup',
        );

        $workPlan = ['foo' => 'bar', 'first' => $first, 'second' => $second];
        $this->assertInstanceOf(
            BowlInterface::class,
            $bowl->execute($this->createStub(ChefInterface::class), $workPlan),
        );

        $this->assertSame($first, $seen);
        $this->assertEquals(['foo' => 'bar', 'first' => $first, 'second' => $second], $workPlan);
    }
}
