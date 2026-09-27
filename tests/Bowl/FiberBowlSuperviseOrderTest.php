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
use Fiber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\DynamicFiberBowl;
use Teknoo\Recipe\Bowl\FiberBowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;

/**
 * Regression tests about the order between the parameters extraction and the registration of the fiber into the
 * cooking supervisor.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FiberBowl::class)]
#[CoversClass(DynamicFiberBowl::class)]
final class FiberBowlSuperviseOrderTest extends TestCase
{
    public function testFiberIsNotSupervisedWhenAParameterIsMissing(): void
    {
        $supervisor = $this->createMock(CookingSupervisorInterface::class);
        $supervisor->expects($this->never())->method('supervise');

        $bowl = new FiberBowl(
            static function (string $missing): void {
            },
            [],
            'fiber',
        );

        $workPlan = [];
        $this->expectException(BadMethodCallException::class);
        $bowl->execute($this->createStub(ChefInterface::class), $workPlan, $supervisor);
    }

    public function testDynamicFiberIsNotSupervisedWhenAParameterIsMissing(): void
    {
        $supervisor = $this->createMock(CookingSupervisorInterface::class);
        $supervisor->expects($this->never())->method('supervise');

        $bowl = new DynamicFiberBowl('callableToExec', true, [], 'fiber');

        $workPlan = [
            'callableToExec' => static function (string $missing): void {
            },
        ];
        $this->expectException(BadMethodCallException::class);
        $bowl->execute($this->createStub(ChefInterface::class), $workPlan, $supervisor);
    }

    public function testFiberIsSupervisedBeforeStartWhenParametersAreValid(): void
    {
        $supervised = null;
        $supervisor = $this->createMock(CookingSupervisorInterface::class);
        $supervisor->expects($this->once())
            ->method('supervise')
            ->willReturnCallback(function (Fiber $fiber) use (&$supervised, $supervisor): CookingSupervisorInterface {
                $this->assertFalse($fiber->isStarted(), 'The fiber must be supervised before its start');
                $supervised = $fiber;

                return $supervisor;
            });

        $bowl = new FiberBowl(
            static function (string $present): void {
                Fiber::suspend($present);
            },
            [],
            'fiber',
        );

        $workPlan = ['present' => 'foo'];
        $this->assertInstanceOf(
            FiberBowl::class,
            $bowl->execute($this->createStub(ChefInterface::class), $workPlan, $supervisor),
        );

        $this->assertInstanceOf(Fiber::class, $supervised);
        $this->assertTrue($supervised->isSuspended());
    }
}
