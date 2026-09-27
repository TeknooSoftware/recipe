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

namespace Teknoo\Tests\Recipe;

use Fiber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use RuntimeException;
use Teknoo\Recipe\CookingSupervisor;
use Teknoo\Recipe\CookingSupervisor\FiberIterator;
use Teknoo\Recipe\CookingSupervisorInterface;

/**
 * Regression tests about the cooking supervisor, executed with real fibers and a real fiber iterator.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(CookingSupervisor::class)]
#[CoversClass(FiberIterator::class)]
final class CookingSupervisorRegressionTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $trace = [];

    /**
     * To create a started fiber, suspended $steps times, tracing each resume as "{$name}{$i}"
     *
     * @return Fiber<mixed, mixed, void, mixed>
     */
    private function startedFiber(string $name, int $steps): Fiber
    {
        $fiber = new Fiber(function () use ($name, $steps): void {
            for ($i = 1; $i <= $steps; ++$i) {
                Fiber::suspend();
                $this->trace[] = $name . $i;
            }
        });

        $fiber->start();

        return $fiber;
    }

    /**
     * @param array<Fiber<mixed, mixed, void, mixed>> $fibers
     */
    private function buildSupervisor(array $fibers): CookingSupervisorInterface
    {
        $supervisor = new CookingSupervisor();
        foreach ($fibers as $fiber) {
            $supervisor->supervise($fiber);
        }

        return $supervisor;
    }

    public function testLoopDoesNotResumeEarlierFibersWhenAMiddleFiberTerminates(): void
    {
        $supervisor = $this->buildSupervisor([
            $this->startedFiber('A', 3),
            $this->startedFiber('B', 1),
            $this->startedFiber('C', 3),
        ]);

        $this->assertInstanceOf(CookingSupervisorInterface::class, $supervisor->loop());
        $this->assertEquals(['A1', 'B1', 'C1'], $this->trace);

        $supervisor->rewindLoop();
        $supervisor->loop();
        $this->assertEquals(['A1', 'B1', 'C1', 'A2', 'C2'], $this->trace);
    }

    public function testFinishWithMiddleFiberTerminatingResumesInOrder(): void
    {
        $supervisor = $this->buildSupervisor([
            $this->startedFiber('A', 2),
            $this->startedFiber('B', 1),
            $this->startedFiber('C', 2),
        ]);

        $this->assertInstanceOf(CookingSupervisorInterface::class, $supervisor->finish());
        $this->assertEquals(['A1', 'B1', 'C1', 'A2', 'C2'], $this->trace);
    }

    public function testThrowWithNullOnSuspendedFiberDoesNothing(): void
    {
        $fiber = $this->startedFiber('A', 1);
        $supervisor = $this->buildSupervisor([$fiber]);

        $this->assertInstanceOf(CookingSupervisorInterface::class, $supervisor->throw(null));

        $this->assertTrue($fiber->isSuspended());
        $this->assertEmpty($this->trace);

        $supervisor->finish();
        $this->assertEquals(['A1'], $this->trace);
        $this->assertTrue($fiber->isTerminated());
    }

    public function testThrowWithNullIsForwardedToSubSupervisors(): void
    {
        $subSupervisor = $this->createMock(CookingSupervisorInterface::class);
        $subSupervisor->expects($this->once())
            ->method('throw')
            ->with(null)
            ->willReturnSelf();

        $supervisor = new CookingSupervisor();
        $supervisor->manage($subSupervisor);

        $this->assertInstanceOf(CookingSupervisorInterface::class, $supervisor->throw());
    }

    public function testThrowWithAnExceptionResumesTheSuspendedFiber(): void
    {
        $fiber = new Fiber(function (): void {
            try {
                Fiber::suspend();
            } catch (RuntimeException $error) {
                $this->trace[] = 'caught ' . $error->getMessage();
            }
        });
        $fiber->start();

        $supervisor = $this->buildSupervisor([$fiber]);
        $supervisor->throw(new RuntimeException('boom'));

        $this->assertEquals(['caught boom'], $this->trace);
        $this->assertTrue($fiber->isTerminated());
    }

    public function testFinishRemovesUnstartedFibers(): void
    {
        //Without the fix, finish() loops indefinitely on the never started fiber : the time limit avoids a hang
        set_time_limit(10);

        $unstarted = new Fiber(function (): void {
        });

        $supervisor = $this->buildSupervisor([
            $unstarted,
            $this->startedFiber('A', 1),
        ]);

        $this->assertInstanceOf(CookingSupervisorInterface::class, $supervisor->finish());

        $this->assertEquals(['A1'], $this->trace);
        $this->assertFalse($unstarted->isStarted());

        $items = (new ReflectionProperty(CookingSupervisor::class, 'items'))->getValue($supervisor);
        $this->assertInstanceOf(FiberIterator::class, $items);
        $this->assertEquals(0, $items->count());

        set_time_limit(0);
    }
}
