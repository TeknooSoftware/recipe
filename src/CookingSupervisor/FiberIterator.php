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

namespace Teknoo\Recipe\CookingSupervisor;

use Countable;
use Fiber;
use Iterator;
use Teknoo\Recipe\CookingSupervisorInterface;

use function count;

/**
 * Iterator to manage list of fibers, with an abstraction for supervisor of the complexity of the list.
 *
 * The iteration is driven by an explicit cursor (and not by the internal pointer of the PHP array) : removing an
 * item during an iteration (a terminated fiber) must not rewind the iteration to the top of the list.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements Iterator<Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface>
 */
class FiberIterator implements Iterator, Countable
{
    /**
     * @var array<int, Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface>
     */
    private array $items = [];

    private int $cursor = 0;

    public function __clone()
    {
        $this->items = [];
        $this->cursor = 0;
    }

    /**
     * @param Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface $item
     */
    public function add(Fiber|CookingSupervisorInterface $item): self
    {
        $this->items[] = $item;

        return $this;
    }

    /**
     * @param Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface $item
     */
    /**
     * Remove all occurrences of the item from the list, without alter the iteration : the cursor is moved back for
     * each occurrence removed before it, so the next item to iterate stays the same.
     *
     * @param Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface $item
     */
    public function remove(Fiber|CookingSupervisorInterface $item): self
    {
        $kept = [];
        $removedBeforeCursor = 0;
        foreach ($this->items as $index => $current) {
            if ($current === $item) {
                if ($index < $this->cursor) {
                    ++$removedBeforeCursor;
                }

                continue;
            }

            $kept[] = $current;
        }

        $this->items = $kept;
        $this->cursor -= $removedBeforeCursor;

        return $this;
    }

    /**
     * @return Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface|null
     */
    public function current(): Fiber|CookingSupervisorInterface|null
    {
        return $this->items[$this->cursor] ?? null;
    }

    public function next(): void
    {
        //The cursor can not go beyond the end of the list, an item added later must be reachable
        if ($this->cursor < count($this->items)) {
            ++$this->cursor;
        }
    }

    public function key(): ?int
    {
        if (!isset($this->items[$this->cursor])) {
            return null;
        }

        return $this->cursor;
    }

    public function valid(): bool
    {
        return isset($this->items[$this->cursor]);
    }

    public function rewind(): void
    {
        $this->cursor = 0;
    }

    public function count(): int
    {
        return count($this->items);
    }
}
