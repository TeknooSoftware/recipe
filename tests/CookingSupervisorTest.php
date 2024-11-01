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
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe;

use RuntimeException;
use Exception;
use Fiber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\Recipe\CookingSupervisor;
use Teknoo\Recipe\CookingSupervisor\Action;
use Teknoo\Recipe\CookingSupervisor\FiberIterator;
use Teknoo\Recipe\CookingSupervisorInterface;
use Throwable;
use TypeError;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CookingSupervisor::class)]
#[CoversClass(Action::class)]
final class CookingSupervisorTest extends TestCase
{
    private ?FiberIterator $items = null;
    public function getFiberIterator(): MockObject&FiberIterator
    {
        if (!$this->items instanceof FiberIterator) {
            $this->items = $this->createMock(FiberIterator::class);
        }

        return $this->items;
    }
    public function buildSupervisor(?CookingSupervisorInterface $supervisor = null): CookingSupervisorInterface
    {
        return new CookingSupervisor($supervisor, $this->getFiberIterator());
    }
    public function testClone(): void
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            clone $this->buildSupervisor()
        );
    }
    public function testSetParentSupervisorBadArgumentSupervisor(): void
    {
        $this->expectException(TypeError::class);
        $this->buildSupervisor()->setParentSupervisor(
            new stdClass(),
        );
    }
    public function testSetParentSupervisor(): void
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->setParentSupervisor(
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }
    public function testSuperviseBadArgumentFiber(): void
    {
        $this->expectException(TypeError::class);
        $this->buildSupervisor()->supervise(
            new stdClass(),
        );
    }
    public function testSupervise(): void
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->supervise(
                new Fiber(function (): void {}),
            )
        );
    }
    public function testSuperviseRunningFiber(): void
    {
        $this->expectException(RuntimeException::class);

        $supervisor = $this->buildSupervisor();

        $fiber = new Fiber(function ($fiber) use ($supervisor): void {
            $supervisor->supervise($fiber);
        });

        $fiber->start($fiber);
    }
    public function testManageBadArgumentSupervisor(): void
    {
        $this->expectException(TypeError::class);
        $this->buildSupervisor()->manage(
            new stdClass(),
        );
    }
    public function testManage(): void
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->manage(
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }
    public function testFreeBadArgumentSupervisor(): void
    {
        $this->expectException(TypeError::class);
        $this->buildSupervisor()->manage(
            new stdClass(),
        );
    }
    public function testFree(): void
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->free(
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }
    public function testRewindLoop(): void
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->rewindLoop()
        );
    }
    public function testSwitchEmpty(): void
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch()
        );
    }
    public function testSwitch(): void
    {
        $a = null;
        $f = new Fiber(
            function () use (&$a): void {
                $a = Fiber::suspend();
            }
        );
        $f->start();

        $this->getFiberIterator()->expects($this->any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects($this->any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects($this->any())->method('current')->willReturn($f);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
        self::assertEquals('foo', $a);
    }
    public function testSwitchWithNotStartedFiber(): void
    {
        $a = null;
        $f = new Fiber(
            function () use (&$a): void {
                $a = Fiber::suspend();
            }
        );

        $this->getFiberIterator()->expects($this->any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects($this->any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects($this->any())->method('current')->willReturn($f);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
        self::assertNull($a);
    }
    public function testSwitchWithTerminatedFiber(): void
    {
        $f = new Fiber(
            function (): void {
            }
        );
        $f->start();

        $this->getFiberIterator()->expects($this->any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects($this->any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects($this->any())->method('current')->willReturn($f);
        $this->getFiberIterator()->expects($this->once())->method('remove')->with($f);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
    }
    public function testSwitchWithSupervisor(): void
    {
        $s = $this->createMock(CookingSupervisorInterface::class);
        $s->expects($this->once())->method('loop');

        $this->getFiberIterator()->expects($this->any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects($this->any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects($this->any())->method('current')->willReturn($s);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
    }
    public function testThrowEmpty(): void
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->throw(new Exception(''))
        );
    }
    public function testThrow(): void
    {
        $a = null;
        $f = new Fiber(
            function () use (&$a): void {
                try {
                    Fiber::suspend();
                } catch (Throwable $error) {
                    $a = $error->getMessage();
                }
            }
        );
        $f->start();

        $this->getFiberIterator()->expects($this->any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects($this->any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects($this->any())->method('current')->willReturn($f);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->throw(new Exception('foo'))
        );
        self::assertEquals('foo', $a);
    }
    public function testThrowWithSupervisor(): void
    {
        $s = $this->createMock(CookingSupervisorInterface::class);
        $s->expects($this->once())->method('throw');

        $this->getFiberIterator()->expects($this->any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects($this->any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects($this->any())->method('current')->willReturn($s);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->throw(new Exception('foo'))
        );
    }
    public function testLoopEmpty(): void
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->loop()
        );
    }
    public function testLoop(): void
    {
        $a = null;
        $f1 = new Fiber(
            function () use (&$a): void {
                Fiber::suspend();
                $a = 'foo';
            }
        );
        $f1->start();

        $b = null;
        $f2 = new Fiber(
            function () use (&$b): void {
                Fiber::suspend();
                $b = 'bar';
                Fiber::suspend();
                $b .= 'bar';
            }
        );
        $f2->start();

        $this->getFiberIterator()->expects($this->any())->method('count')->willReturn(2);
        $this->getFiberIterator()->expects($this->any())->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false);
        $this->getFiberIterator()
            ->expects($this->any())
            ->method('current')
            ->willReturnOnConsecutiveCalls($f1, $f2);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->loop()
        );
        self::assertEquals('foo', $a);
        self::assertEquals('bar', $b);
    }
    public function testLoopWithSupervisor(): void
    {
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s1->expects($this->once())->method('loop');

        $s2 = $this->createMock(CookingSupervisorInterface::class);
        $s2->expects($this->once())->method('loop');

        $this->getFiberIterator()->expects($this->any())->method('count')->willReturn(2);
        $this->getFiberIterator()->expects($this->any())->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false)
            ->willReturn(true);
        $this->getFiberIterator()->expects($this->any())->method('current')
            ->willReturnOnConsecutiveCalls($s1, $s2, null, null, null);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->loop()
        );
    }
    public function testFinishEmpty(): void
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->finish()
        );
    }
    public function testFinish(): void
    {
        $a = null;
        $f1 = new Fiber(
            function () use (&$a): void {
                Fiber::suspend();
                $a = 'foo';
            }
        );
        $f1->start();

        $b = null;
        $f2 = new Fiber(
            function () use (&$b): void {
                Fiber::suspend();
                $b = 'bar';
                Fiber::suspend();
                $b .= 'bar';
            }
        );
        $f2->start();

        $this->getFiberIterator()->expects($this->any())->method('count')->willReturn(2);
        $this->getFiberIterator()->expects($this->exactly(2))->method('remove')
            ->with(
                $this->callback(
                    fn ($value): bool => match ($value) {
                        $f1 => true,
                        $f2 => true,
                        default => false,
                    }
                )
            );
        $this->getFiberIterator()->expects($this->any())->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false);
        $this->getFiberIterator()
            ->expects($this->any())
            ->method('current')
            ->willReturnOnConsecutiveCalls($f1, $f2, $f2, null, null);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->finish()
        );
        self::assertEquals('foo', $a);
        self::assertEquals('barbar', $b);
    }
    public function testFinishWithSupervisor(): void
    {
        $supervisor = $this->buildSupervisor();

        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s1->expects($this->once())->method('finish')->willReturnCallback(
            function () use ($supervisor, $s1): MockObject {
                $supervisor->free($s1);
                return $s1;
            }
        );

        $s2 = $this->createMock(CookingSupervisorInterface::class);
        $s2->expects($this->once())->method('finish')->willReturnCallback(
            function () use ($supervisor, $s2): MockObject {
                $supervisor->free($s2);
                return $s2;
            }
        );

        $this->getFiberIterator()->expects($this->any())->method('count')->willReturn(2);
        $this->getFiberIterator()->expects($this->exactly(2))->method('remove')
            ->with(
                $this->callback(
                    fn ($value): bool => match ($value) {
                        $s1 => true,
                        $s2 => true,
                        default => false,
                    }
                )
            );
        $this->getFiberIterator()->expects($this->any())->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false);
        $this->getFiberIterator()->expects($this->any())->method('current')
            ->willReturnOnConsecutiveCalls($s1, $s2, null, null, null);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $supervisor->finish()
        );
    }
    public function testFinishInSupervised(): void
    {
        $manager = $this->createMock(CookingSupervisorInterface::class);

        $supervisor = $this->buildSupervisor($manager);

        $manager->expects($this->once())->method('free')->with($supervisor);

        $this->getFiberIterator()->expects($this->any())->method('count')->willReturn(0);
        $this->getFiberIterator()->expects($this->any())->method('valid')
            ->willReturn(false);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $supervisor->finish()
        );
    }
    public function testIntegration(): void
    {
        $a = null;
        $f1 = new Fiber(function () use (&$a): void {
            $a = Fiber::suspend();
            $s = 1;
        });
        $f1->start();

        $b = null;
        $f2 = new Fiber(
            function () use (&$b): void {
                $b = Fiber::suspend();
                $s = 1;
                $b .= Fiber::suspend();
                $s = 1;
                $b .= Fiber::suspend();
                $s = 1;
                $b .= Fiber::suspend();
                $s = 1;
            }
        );
        $f2->start();

        $c = null;
        $f3 = new Fiber(function () use (&$c): void {
            $c = Fiber::suspend();
            $s = 1;
            $c .= Fiber::suspend();
            $s = 1;
            $c .= Fiber::suspend();
            $s = 1;
        });
        $f3->start();

        $d = null;
        $f4 = new Fiber(function () use (&$d): void {
            $d = Fiber::suspend();
            $s = 1;
        });
        $f4->start();

        $e = null;
        $f5 = new Fiber(function () use (&$e): void {
            $e = Fiber::suspend();
            $s = 1;
        });
        $f5->start();

        $supervisor1 = new CookingSupervisor();
        $supervisor2 = new CookingSupervisor($supervisor1);
        $supervisor3 = new CookingSupervisor($supervisor1);
        $supervisor4 = new CookingSupervisor($supervisor2);

        $supervisor1->supervise($f1);
        $supervisor1->manage($supervisor2);
        $supervisor1->manage($supervisor3);
        $supervisor2->supervise($f2);
        $supervisor2->supervise($f3);
        $supervisor2->manage($supervisor4);
        $supervisor3->supervise($f4);
        $supervisor4->supervise($f5);

        self::assertNull($a);
        self::assertNull($b);
        self::assertNull($c);
        self::assertNull($d);
        self::assertNull($e);

        $supervisor1->switch('foo');

        self::assertEquals('foo', $a);
        self::assertNull($b);
        self::assertNull($c);
        self::assertNull($d);
        self::assertNull($e);

        $supervisor1->switch('bar');

        self::assertEquals('foo', $a);
        self::assertEquals('bar', $b);
        self::assertEquals('bar', $c);
        self::assertNull($d);
        self::assertEquals('bar', $e);

        $supervisor1->loop('f00');

        self::assertEquals('foo', $a);
        self::assertEquals('bar', $b);
        self::assertEquals('bar', $c);
        self::assertEquals('f00', $d);
        self::assertEquals('bar', $e);

        $supervisor1->loop('baar');

        self::assertEquals('foo', $a);
        self::assertEquals('barbaar', $b);
        self::assertEquals('barbaar', $c);
        self::assertEquals('f00', $d);
        self::assertEquals('bar', $e);

        $supervisor1->finish('bye');

        self::assertEquals('foo', $a);
        self::assertEquals('barbaarbyebye', $b);
        self::assertEquals('barbaarbye', $c);
        self::assertEquals('f00', $d);
        self::assertEquals('bar', $e);
    }
}
