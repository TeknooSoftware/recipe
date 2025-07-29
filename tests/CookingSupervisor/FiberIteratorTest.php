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

namespace Teknoo\Tests\Recipe\CookingSupervisor;

use Fiber;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\Recipe\CookingSupervisor\FiberIterator;
use Teknoo\Recipe\CookingSupervisorInterface;
use TypeError;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(FiberIterator::class)]
final class FiberIteratorTest extends TestCase
{
    public function buildIterator(array $list): FiberIterator
    {
        $iterator = new FiberIterator();

        foreach ($list as $item) {
            $iterator->add($item);
        }

        return $iterator;
    }
    public function testAddBadItem(): void
    {
        $this->expectException(TypeError::class);
        $this->buildIterator([])->add(
            new stdClass(),
        );
    }
    public function testAdd(): void
    {
        $this->assertInstanceOf(
            FiberIterator::class,
            $this->buildIterator([])->add(
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }
    public function testRemoveBadItem(): void
    {
        $this->expectException(TypeError::class);
        $this->buildIterator([])->remove(
            new stdClass(),
        );
    }
    public function testRemove(): void
    {
        $this->buildIterator([])->remove(
            new Fiber(function (): void {})
        );

        $f1 = new Fiber(function (): void {});
        $this->buildIterator([$f1])->remove(
            $f1
        );

        $f1 = new Fiber(function (): void {});

        $this->assertInstanceOf(
            FiberIterator::class,
            $this->buildIterator([$f1])->remove(
                new Fiber(function (): void {})
            )
        );
    }
    public function testCurrentEmptyList(): void
    {
        $this->assertEmpty(
            $this->buildIterator([])->current()
        );
    }
    public function testCurrentStartOfList(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $this->assertSame(
            $f1,
            $iterator->current()
        );
    }
    public function testCurrentEndOfList(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();

        $this->assertNull(
            $iterator->current()
        );
    }
    public function testCurrent(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $iterator->next();

        $this->assertSame(
            $s1,
            $iterator->current()
        );
    }
    public function testNextEmptyList(): void
    {
        $iterator = $this->buildIterator([]);
        $iterator->next();
        $this->assertFalse($iterator->valid());
    }
    public function testNextStartOfList(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $result = $iterator->current();
        $this->assertSame(
            $f2,
            $result
        );
    }
    public function testNextEndOfList(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();

        $this->assertNull(
            $iterator->current()
        );
    }
    public function testNextWithRemovedElement(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->remove($f1);
        $iterator->remove($s1);
        $iterator->remove($f3);
        $iterator->next();

        $this->assertSame(
            $f4,
            $iterator->current()
        );
    }
    public function testKey(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $this->assertEquals(
            1,
            $iterator->key()
        );

        $iterator->next();
        $this->assertEquals(
            2,
            $iterator->key()
        );

        $iterator->next();
        $iterator->next();
        $iterator->next();
        $this->assertEquals(
            5,
            $iterator->key()
        );

        $iterator->next();
        $this->assertNull(
            $iterator->key()
        );
    }
    public function testValidEmptyList(): void
    {
        $this->assertFalse(
            $this->buildIterator([])->valid()
        );
    }
    public function testValidStartOfList(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $this->assertTrue(
            $iterator->valid()
        );
    }
    public function testValidEndOfList(): void
    {

        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();

        $this->assertFalse(
            $iterator->valid()
        );
    }
    public function testValid(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $iterator->next();

        $this->assertTrue(
            $iterator->valid()
        );
    }
    public function testCount(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $this->assertEquals(
            6,
            $iterator->count()
        );
    }
    public function testRewindEmpty(): void
    {
        $iterator = $this->buildIterator([]);
        $iterator->rewind();
        $this->assertNull(
            $iterator->current()
        );
    }
    public function testRewind(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $iterator->next();
        $iterator->rewind();

        $this->assertSame(
            $f1,
            $iterator->current()
        );
    }
    public function testRewindWithEmptyStack(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});
        $f4 = new Fiber(function (): void {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->next();
        $iterator->remove($f1);
        $iterator->remove($s1);
        $iterator->rewind();

        $this->assertSame(
            $f2,
            $iterator->current()
        );
    }
    public function testClone(): void
    {
        $f1 = new Fiber(function (): void {});
        $f2 = new Fiber(function (): void {});
        $f3 = new Fiber(function (): void {});

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$f3
            ]
        );

        $this->assertEquals(
            3,
            $iterator->count()
        );

        $this->assertEquals(
            0,
            (clone $iterator)->count()
        );
    }
}
