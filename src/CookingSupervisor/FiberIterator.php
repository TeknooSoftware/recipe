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

namespace Teknoo\Recipe\CookingSupervisor;

use Countable;
use Fiber;
use Iterator;
use Teknoo\Recipe\CookingSupervisorInterface;

use function array_splice;
use function count;
use function current;
use function key;
use function reset;

/**
 * Iterator to manage list of fibers, with an abstraction for supervisor of the complexity of the list.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @implements Iterator<Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface>>
 */
class FiberIterator implements Iterator, Countable
{
    /**
     * @var array<int, Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface>
     */
    private array $items = [];

    public function __clone()
    {
        $this->items = [];
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
    public function remove(Fiber|CookingSupervisorInterface $item): self
    {
        $count = count($this->items);
        for ($i = 0; $i < $count; ++$i) {
            if ($this->items[$i] !== $item) {
                continue;
            }

            array_splice($this->items, $i, 1);
            $count = count($this->items);
        }

        return $this;
    }

    /**
     * @return Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface|null
     */
    public function current(): Fiber|CookingSupervisorInterface|null
    {
        if (empty($this->items)) {
            return null;
        }

        if (false === ($item = current($this->items))) {
            return null;
        }

        return $item;
    }

    public function next(): void
    {
        if (empty($this->items)) {
            return;
        }

        next($this->items);
    }

    public function key(): ?int
    {
        return key($this->items);
    }

    public function valid(): bool
    {
        return false !== current($this->items);
    }

    public function rewind(): void
    {
        if (empty($this->items)) {
            return;
        }

        reset($this->items);
    }

    public function count(): int
    {
        return count($this->items);
    }
}
