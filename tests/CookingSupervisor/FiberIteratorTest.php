<?php

/**
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

namespace Teknoo\Tests\Recipe\CookingSupervisor;

use Fiber;
use PHPUnit\Framework\TestCase;
use stdClass;
use Teknoo\Recipe\CookingSupervisor\FiberIterator;
use Teknoo\Recipe\CookingSupervisorInterface;
use TypeError;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\CookingSupervisor\FiberIterator
 */
class FiberIteratorTest extends TestCase
{
    public function buildIterator(array $list): FiberIterator
    {
        $iterator = new FiberIterator();

        foreach ($list as $item) {
            $iterator->add($item);
        }

        return $iterator;
    }

    public function testAddBadItem()
    {
        $this->expectException(TypeError::class);
        $this->buildIterator([])->add(
            new stdClass(),
        );
    }

    public function testAdd()
    {
        self::assertInstanceOf(
            FiberIterator::class,
            $this->buildIterator([])->add(
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    public function testRemoveBadItem()
    {
        $this->expectException(TypeError::class);
        $this->buildIterator([])->remove(
            new stdClass(),
        );
    }

    public function testRemove()
    {
        $this->buildIterator([])->remove(
            new Fiber(function() {})
        );

        $f1 = new Fiber(function() {});
        $this->buildIterator([$f1])->remove(
            $f1
        );

        $f1 = new Fiber(function() {});

        self::assertInstanceOf(
            FiberIterator::class,
            $this->buildIterator([$f1])->remove(
                new Fiber(function() {})
            )
        );
    }

    public function testCurrentEmptyList()
    {
        self::assertEmpty(
            $this->buildIterator([])->current()
        );
    }

    public function testCurrentStartOfList()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        self::assertSame(
            $f1,
            $iterator->current()
        );
    }

    public function testCurrentEndOfList()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
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

        self::assertNull(
            $iterator->current()
        );
    }
    
    public function testCurrent()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $iterator->next();

        self::assertSame(
            $s1,
            $iterator->current()
        );
    }

    public function testNextEmptyList()
    {
        $iterator = $this->buildIterator([]);
        $iterator->next();
        self::assertFalse($iterator->valid());
    }

    public function testNextStartOfList()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $result = $iterator->current();
        self::assertSame(
            $f2,
            $result
        );
    }

    public function testNextEndOfList()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
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

        self::assertNull(
            $iterator->current()
        );
    }

    public function testNextWithRemovedElement()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
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

        self::assertSame(
            $f4,
            $iterator->current()
        );
    }

    public function testKey()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        self::assertEquals(
            1,
            $iterator->key()
        );

        $iterator->next();
        self::assertEquals(
            2,
            $iterator->key()
        );

        $iterator->next();
        $iterator->next();
        $iterator->next();
        self::assertEquals(
            5,
            $iterator->key()
        );

        $iterator->next();
        self::assertNull(
            $iterator->key()
        );
    }

    public function testValidEmptyList()
    {
        self::assertFalse(
            $this->buildIterator([])->valid()
        );
    }

    public function testValidStartOfList()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        self::assertTrue(
            $iterator->valid()
        );
    }

    public function testValidEndOfList()
    {

        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
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

        self::assertFalse(
            $iterator->valid()
        );
    }

    public function testValid()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        $iterator->next();
        $iterator->next();

        self::assertTrue(
            $iterator->valid()
        );
    }

    public function testCount()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
        $s1 = $this->createMock(CookingSupervisorInterface::class);
        $s2 = $this->createMock(CookingSupervisorInterface::class);

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$s1,$f3,$f4, $s2
            ]
        );

        self::assertEquals(
            6,
            $iterator->count()
        );
    }

    public function testRewindEmpty()
    {
        $iterator = $this->buildIterator([]);
        $iterator->rewind();
        self::assertNull(
            $iterator->current()
        );
    }

    public function testRewind()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
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

        self::assertSame(
            $f1,
            $iterator->current()
        );
    }

    public function testRewindWithEmptyStack()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});
        $f4 = new Fiber(function() {});
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

        self::assertSame(
            $f2,
            $iterator->current()
        );
    }

    public function testClone()
    {
        $f1 = new Fiber(function() {});
        $f2 = new Fiber(function() {});
        $f3 = new Fiber(function() {});

        $iterator = $this->buildIterator(
            [
                $f1,$f2,$f3
            ]
        );

        self::assertEquals(
            3,
            $iterator->count()
        );

        self::assertEquals(
            0,
            (clone $iterator)->count()
        );
    }
}
