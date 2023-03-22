<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe;

use Exception;
use Fiber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\Recipe\CookingSupervisor;
use Teknoo\Recipe\CookingSupervisor\FiberIterator;
use Teknoo\Recipe\CookingSupervisorInterface;
use Throwable;
use TypeError;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\CookingSupervisor
 * @covers \Teknoo\Recipe\CookingSupervisor\Action
 */
class CookingSupervisorTest extends TestCase
{
    private ?FiberIterator $items = null;

    public function getFiberIterator(): MockObject&FiberIterator
    {
        if (null === $this->items) {
            $this->items = $this->createMock(FiberIterator::class);
        }

        return $this->items;
    }

    public function buildSupervisor(?CookingSupervisorInterface $supervisor = null): CookingSupervisorInterface
    {
        return new CookingSupervisor($supervisor, $this->getFiberIterator());
    }

    public function testClone()
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            clone $this->buildSupervisor()
        );
    }

    public function testSetParentSupervisorBadArgumentSupervisor()
    {
        $this->expectException(TypeError::class);
        $this->buildSupervisor()->setParentSupervisor(
            new stdClass(),
        );
    }

    public function testSetParentSupervisor()
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->setParentSupervisor(
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    public function testSuperviseBadArgumentFiber()
    {
        $this->expectException(TypeError::class);
        $this->buildSupervisor()->supervise(
            new stdClass(),
        );
    }

    public function testSupervise()
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->supervise(
                new Fiber(function() {}),
            )
        );
    }

    public function testSuperviseRunningFiber()
    {
        $this->expectException(\RuntimeException::class);
        
        $supervisor = $this->buildSupervisor();

        $fiber = new Fiber(function($fiber) use ($supervisor) {
            $supervisor->supervise($fiber);
        });

        $fiber->start($fiber);
    }

    public function testManageBadArgumentSupervisor()
    {
        $this->expectException(TypeError::class);
        $this->buildSupervisor()->manage(
            new stdClass(),
        );
    }

    public function testManage()
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->manage(
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    public function testFreeBadArgumentSupervisor()
    {
        $this->expectException(TypeError::class);
        $this->buildSupervisor()->manage(
            new stdClass(),
        );
    }

    public function testFree()
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->free(
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    public function testRewindLoop()
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->rewindLoop()
        );
    }

    public function testSwitchEmpty()
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch()
        );
    }

    public function testSwitch()
    {
        $a = null;
        $f = new Fiber(
            function () use (&$a) {
                $a = Fiber::suspend();
            }
        );
        $f->start();

        $this->getFiberIterator()->expects(self::any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects(self::any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects(self::any())->method('current')->willReturn($f);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
        self::assertEquals('foo', $a);
    }

    public function testSwitchWithNotStartedFiber()
    {
        $a = null;
        $f = new Fiber(
            function () use (&$a) {
                $a = Fiber::suspend();
            }
        );

        $this->getFiberIterator()->expects(self::any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects(self::any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects(self::any())->method('current')->willReturn($f);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
        self::assertNull($a);
    }

    public function testSwitchWithTerminatedFiber()
    {
        $f = new Fiber(
            function () {
            }
        );
        $f->start();

        $this->getFiberIterator()->expects(self::any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects(self::any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects(self::any())->method('current')->willReturn($f);
        $this->getFiberIterator()->expects(self::once())->method('remove')->with($f);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
    }

    public function testSwitchWithSupervisor()
    {
        $s = $this->createMock(CookingSupervisorInterface::class);
        $s->expects(self::once())->method('loop');

        $this->getFiberIterator()->expects(self::any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects(self::any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects(self::any())->method('current')->willReturn($s);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->switch('foo')
        );
    }

    public function testThrowEmpty()
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->throw(new Exception(''))
        );
    }

    public function testThrow()
    {
        $a = null;
        $f = new Fiber(
            function () use (&$a) {
                try {
                    Fiber::suspend();
                } catch (Throwable $error) {
                    $a = $error->getMessage();
                }
            }
        );
        $f->start();

        $this->getFiberIterator()->expects(self::any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects(self::any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects(self::any())->method('current')->willReturn($f);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->throw(new Exception('foo'))
        );
        self::assertEquals('foo', $a);
    }

    public function testThrowWithSupervisor()
    {
        $s = $this->createMock(CookingSupervisorInterface::class);
        $s->expects(self::once())->method('throw');

        $this->getFiberIterator()->expects(self::any())->method('count')->willReturn(1);
        $this->getFiberIterator()->expects(self::any())->method('valid')->willReturn(true);
        $this->getFiberIterator()->expects(self::any())->method('current')->willReturn($s);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->throw(new Exception('foo'))
        );
    }

    public function testLoopEmpty()
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->loop()
        );
    }

    public function testLoop()
    {
        $a = null;
        $f1 = new Fiber(
            function () use (&$a) {
                Fiber::suspend();
                $a = 'foo';
            }
        );
        $f1->start();

        $b = null;
        $f2 = new Fiber(
            function () use (&$b) {
                Fiber::suspend();
                $b = 'bar';
                Fiber::suspend();
                $b .= 'bar';
            }
        );
        $f2->start();

        $this->getFiberIterator()->expects(self::any())->method('count')->willReturn(2);
        $this->getFiberIterator()->expects(self::any())->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false);
        $this->getFiberIterator()
            ->expects(self::any())
            ->method('current')
            ->willReturnOnConsecutiveCalls($f1, $f2);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->loop()
        );
        self::assertEquals('foo', $a);
        self::assertEquals('bar', $b);
    }

    public function testLoopWithSupervisor()
    {
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s1->expects(self::once())->method('loop');

        $s2 = $this->createMock(CookingSupervisorInterface::class);
        $s2->expects(self::once())->method('loop');

        $this->getFiberIterator()->expects(self::any())->method('count')->willReturn(2);
        $this->getFiberIterator()->expects(self::any())->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false)
            ->willReturn(true);
        $this->getFiberIterator()->expects(self::any())->method('current')
            ->willReturnOnConsecutiveCalls($s1, $s2);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->loop()
        );
    }

    public function testFinishEmpty()
    {
        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->finish()
        );
    }

    public function testFinish()
    {
        $a = null;
        $f1 = new Fiber(
            function () use (&$a) {
                Fiber::suspend();
                $a = 'foo';
            }
        );
        $f1->start();

        $b = null;
        $f2 = new Fiber(
            function () use (&$b) {
                Fiber::suspend();
                $b = 'bar';
                Fiber::suspend();
                $b .= 'bar';
            }
        );
        $f2->start();

        $this->getFiberIterator()->expects(self::any())->method('count')->willReturn(2);
        $this->getFiberIterator()->expects(self::exactly(2))->method('remove')
            ->with(
                $this->callback(
                    fn ($value) => match ($value) {
                        $f1 => true,
                        $f2 => true,
                        default => false,
                    }
                )
            );
        $this->getFiberIterator()->expects(self::any())->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false);
        $this->getFiberIterator()
            ->expects(self::any())
            ->method('current')
            ->willReturnOnConsecutiveCalls($f1, $f2, $f2);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $this->buildSupervisor()->finish()
        );
        self::assertEquals('foo', $a);
        self::assertEquals('barbar', $b);
    }

    public function testFinishWithSupervisor()
    {
        $supervisor = $this->buildSupervisor();

        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s1->expects(self::once())->method('finish')->willReturnCallback(
            function () use ($supervisor, $s1) {
                $supervisor->free($s1);
                return $s1;
            }
        );

        $s2 = $this->createMock(CookingSupervisorInterface::class);
        $s2->expects(self::once())->method('finish')->willReturnCallback(
            function () use ($supervisor, $s2) {
                $supervisor->free($s2);
                return $s2;
            }
        );

        $this->getFiberIterator()->expects(self::any())->method('count')->willReturn(2);
        $this->getFiberIterator()->expects(self::exactly(2))->method('remove')
            ->with(
                $this->callback(
                    fn ($value) => match ($value) {
                        $s1 => true,
                        $s2 => true,
                        default => false,
                    }
                )
            );
        $this->getFiberIterator()->expects(self::any())->method('valid')
            ->willReturnOnConsecutiveCalls(true, true, false, true, false);
        $this->getFiberIterator()->expects(self::any())->method('current')
            ->willReturnOnConsecutiveCalls($s1, $s2);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $supervisor->finish()
        );
    }

    public function testFinishInSupervised()
    {
        $manager = $this->createMock(CookingSupervisorInterface::class);

        $supervisor = $this->buildSupervisor($manager);

        $manager->expects(self::once())->method('free')->with($supervisor);

        $this->getFiberIterator()->expects(self::any())->method('count')->willReturn(0);
        $this->getFiberIterator()->expects(self::any())->method('valid')
            ->willReturn(false);

        self::assertInstanceOf(
            CookingSupervisorInterface::class,
            $supervisor->finish()
        );
    }

    public function testIntegration()
    {
        $a = null;
        $f1 = new Fiber(function () use (&$a) {
            $a = Fiber::suspend();
            $s = 1;
        });
        $f1->start();

        $b = null;
        $f2 = new Fiber(
            function () use (&$b) {
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
        $f3 = new Fiber(function () use (&$c) {
            $c = Fiber::suspend();
            $s = 1;
            $c .= Fiber::suspend();
            $s = 1;
            $c .= Fiber::suspend();
            $s = 1;
        });
        $f3->start();

        $d = null;
        $f4 = new Fiber(function () use (&$d) {
            $d = Fiber::suspend();
            $s = 1;
        });
        $f4->start();

        $e = null;
        $f5 = new Fiber(function () use (&$e) {
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
