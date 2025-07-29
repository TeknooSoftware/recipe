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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(CookingSupervisor::class)]
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
        $this->assertInstanceOf(
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
        $this->assertInstanceOf(
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
        $this->assertInstanceOf(
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
        $this->assertInstanceOf(
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
        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->free(
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }
    public function testRewindLoop(): void
    {
        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->rewindLoop()
        );
    }
    public function testSwitchEmpty(): void
    {
        $this->assertInstanceOf(
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

        $this->getFiberIterator()->method('count')->willReturn(1);
        $this->getFiberIterator()->method('valid')->willReturn(true);
        $this->getFiberIterator()->method('current')->willReturn($f);

        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
        $this->assertEquals('foo', $a);
    }
    public function testSwitchWithNotStartedFiber(): void
    {
        $a = null;
        $f = new Fiber(
            function () use (&$a): void {
                $a = Fiber::suspend();
            }
        );

        $this->getFiberIterator()->method('count')->willReturn(1);
        $this->getFiberIterator()->method('valid')->willReturn(true);
        $this->getFiberIterator()->method('current')->willReturn($f);

        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
        $this->assertNull($a);
    }
    public function testSwitchWithTerminatedFiber(): void
    {
        $f = new Fiber(
            function (): void {
            }
        );
        $f->start();

        $this->getFiberIterator()->method('count')->willReturn(1);
        $this->getFiberIterator()->method('valid')->willReturn(true);
        $this->getFiberIterator()->method('current')->willReturn($f);
        $this->getFiberIterator()->expects($this->once())->method('remove')->with($f);

        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
    }
    public function testSwitchWithSupervisor(): void
    {
        $s = $this->createMock(CookingSupervisorInterface::class);
        $s->expects($this->once())->method('loop');

        $this->getFiberIterator()->method('count')->willReturn(1);
        $this->getFiberIterator()->method('valid')->willReturn(true);
        $this->getFiberIterator()->method('current')->willReturn($s);

        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
    }
    public function testThrowEmpty(): void
    {
        $this->assertInstanceOf(
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

        $this->getFiberIterator()->method('count')->willReturn(1);
        $this->getFiberIterator()->method('valid')->willReturn(true);
        $this->getFiberIterator()->method('current')->willReturn($f);

        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->throw(new Exception('foo'))
        );
        $this->assertEquals('foo', $a);
    }
    public function testThrowWithSupervisor(): void
    {
        $s = $this->createMock(CookingSupervisorInterface::class);
        $s->expects($this->once())->method('throw');

        $this->getFiberIterator()->method('count')->willReturn(1);
        $this->getFiberIterator()->method('valid')->willReturn(true);
        $this->getFiberIterator()->method('current')->willReturn($s);

        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->throw(new Exception('foo'))
        );
    }
    public function testLoopEmpty(): void
    {
        $this->assertInstanceOf(
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

        $this->getFiberIterator()->method('count')->willReturn(2);
        $this->getFiberIterator()->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false);
        $this->getFiberIterator()
            
            ->method('current')
            ->willReturnOnConsecutiveCalls($f1, $f2);

        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->loop()
        );
        $this->assertEquals('foo', $a);
        $this->assertEquals('bar', $b);
    }
    public function testLoopWithSupervisor(): void
    {
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s1->expects($this->once())->method('loop');

        $s2 = $this->createMock(CookingSupervisorInterface::class);
        $s2->expects($this->once())->method('loop');

        $this->getFiberIterator()->method('count')->willReturn(2);
        $this->getFiberIterator()->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false)
            ->willReturn(true);
        $this->getFiberIterator()->method('current')
            ->willReturnOnConsecutiveCalls($s1, $s2, null, null, null);

        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->loop()
        );
    }
    public function testFinishEmpty(): void
    {
        $this->assertInstanceOf(
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

        $this->getFiberIterator()->method('count')->willReturn(2);
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
        $this->getFiberIterator()->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false);
        $this->getFiberIterator()
            
            ->method('current')
            ->willReturnOnConsecutiveCalls($f1, $f2, $f2, null, null);

        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->finish()
        );
        $this->assertEquals('foo', $a);
        $this->assertEquals('barbar', $b);
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

        $this->getFiberIterator()->method('count')->willReturn(2);
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
        $this->getFiberIterator()->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false);
        $this->getFiberIterator()->method('current')
            ->willReturnOnConsecutiveCalls($s1, $s2, null, null, null);

        $this->assertInstanceOf(
            CookingSupervisorInterface::class,
            $supervisor->finish()
        );
    }
    public function testFinishInSupervised(): void
    {
        $manager = $this->createMock(CookingSupervisorInterface::class);

        $supervisor = $this->buildSupervisor($manager);

        $manager->expects($this->once())->method('free')->with($supervisor);

        $this->getFiberIterator()->method('count')->willReturn(0);
        $this->getFiberIterator()->method('valid')
            ->willReturn(false);

        $this->assertInstanceOf(
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

        $this->assertNull($a);
        $this->assertNull($b);
        $this->assertNull($c);
        $this->assertNull($d);
        $this->assertNull($e);

        $supervisor1->switch('foo');

        $this->assertEquals('foo', $a);
        $this->assertNull($b);
        $this->assertNull($c);
        $this->assertNull($d);
        $this->assertNull($e);

        $supervisor1->switch('bar');

        $this->assertEquals('foo', $a);
        $this->assertEquals('bar', $b);
        $this->assertEquals('bar', $c);
        $this->assertNull($d);
        $this->assertEquals('bar', $e);

        $supervisor1->loop('f00');

        $this->assertEquals('foo', $a);
        $this->assertEquals('bar', $b);
        $this->assertEquals('bar', $c);
        $this->assertEquals('f00', $d);
        $this->assertEquals('bar', $e);

        $supervisor1->loop('baar');

        $this->assertEquals('foo', $a);
        $this->assertEquals('barbaar', $b);
        $this->assertEquals('barbaar', $c);
        $this->assertEquals('f00', $d);
        $this->assertEquals('bar', $e);

        $supervisor1->finish('bye');

        $this->assertEquals('foo', $a);
        $this->assertEquals('barbaarbyebye', $b);
        $this->assertEquals('barbaarbye', $c);
        $this->assertEquals('f00', $d);
        $this->assertEquals('bar', $e);
    }
}
