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
use Teknoo\Recipe\CookingSupervisor\FiberIterator;

/**
 * Regression tests about the cursor of the fiber iterator when items are removed during an iteration.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(FiberIterator::class)]
final class FiberIteratorCursorTest extends TestCase
{
    /**
     * @param array<Fiber<mixed, mixed, void, mixed>> $items
     */
    private function buildIterator(array $items): FiberIterator
    {
        $iterator = new FiberIterator();
        foreach ($items as $item) {
            $iterator->add($item);
        }

        return $iterator;
    }

    /**
     * @return Fiber<mixed, mixed, void, mixed>
     */
    private function fiber(): Fiber
    {
        return new Fiber(function (): void {
        });
    }

    public function testRemovingAnEarlierItemDoesNotRewindIteration(): void
    {
        $f1 = $this->fiber();
        $f2 = $this->fiber();
        $f3 = $this->fiber();
        $iterator = $this->buildIterator([$f1, $f2, $f3]);

        $iterator->next();
        $iterator->next();
        $this->assertSame($f3, $iterator->current());

        $iterator->remove($f1);

        $this->assertSame($f3, $iterator->current());
        $this->assertEquals(1, $iterator->key());
        $iterator->next();
        $this->assertFalse($iterator->valid());
    }

    public function testRemovingTheLastConsumedItemKeepsNextItem(): void
    {
        $f1 = $this->fiber();
        $f2 = $this->fiber();
        $f3 = $this->fiber();
        $iterator = $this->buildIterator([$f1, $f2, $f3]);

        $this->assertSame($f1, $iterator->current());
        $iterator->next();
        $iterator->remove($f1);

        $this->assertSame($f2, $iterator->current());
        $iterator->next();
        $this->assertSame($f3, $iterator->current());
    }

    public function testRemovingAnItemAfterTheCursorDoesNotMoveIt(): void
    {
        $f1 = $this->fiber();
        $f2 = $this->fiber();
        $f3 = $this->fiber();
        $iterator = $this->buildIterator([$f1, $f2, $f3]);

        $iterator->next();
        $iterator->remove($f3);

        $this->assertSame($f2, $iterator->current());
        $this->assertEquals(2, $iterator->count());
    }

    public function testRemoveDropsAdjacentDuplicates(): void
    {
        $f1 = $this->fiber();
        $f2 = $this->fiber();
        $iterator = $this->buildIterator([$f1, $f1, $f2, $f1]);

        $iterator->remove($f1);

        $this->assertEquals(1, $iterator->count());
        $this->assertSame($f2, $iterator->current());
    }

    public function testKeyIsNullAfterEndAndZeroAfterRewind(): void
    {
        $iterator = $this->buildIterator([$this->fiber(), $this->fiber()]);

        $iterator->next();
        $iterator->next();
        $iterator->next();
        $this->assertNull($iterator->key());
        $this->assertNull($iterator->current());
        $this->assertFalse($iterator->valid());

        $iterator->rewind();
        $this->assertEquals(0, $iterator->key());
        $this->assertTrue($iterator->valid());
    }

    public function testAnItemAddedAfterTheEndIsReachable(): void
    {
        $f1 = $this->fiber();
        $f2 = $this->fiber();
        $iterator = $this->buildIterator([$f1]);

        $iterator->next();
        $iterator->next();
        $this->assertFalse($iterator->valid());

        $iterator->add($f2);

        $this->assertTrue($iterator->valid());
        $this->assertSame($f2, $iterator->current());
    }

    public function testCloneResetsCursor(): void
    {
        $iterator = $this->buildIterator([$this->fiber(), $this->fiber()]);
        $iterator->next();

        $clone = clone $iterator;
        $f3 = $this->fiber();
        $clone->add($f3);

        $this->assertEquals(0, $clone->count() - 1);
        $this->assertSame($f3, $clone->current());
        $this->assertEquals(0, $clone->key());
    }
}
