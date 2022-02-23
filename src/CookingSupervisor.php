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

namespace Teknoo\Recipe;

use Fiber;
use RuntimeException;
use Teknoo\Recipe\CookingSupervisor\Action;
use Teknoo\Recipe\CookingSupervisor\FiberIterator;
use Throwable;

/**
 * Default implementation of cooking supervisor to manage fibers in a recipe execution
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class CookingSupervisor implements CookingSupervisorInterface
{
    /**
     * @param FiberIterator<Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface> $items
     */
    public function __construct(
        private ?CookingSupervisorInterface $supervisor = null,
        private FiberIterator $items = new FiberIterator(),
    ) {
    }

    public function __clone(): void
    {
        $this->items = clone $this->items;
    }

    public function setParentSupervisor(CookingSupervisorInterface $supervisor): CookingSupervisorInterface
    {
        $this->supervisor = $supervisor;
        $supervisor->manage($this);

        return $this;
    }

    /**
     * @return Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface|null
     */
    private function getNextItem(bool $loopBackToTopOfList): Fiber|CookingSupervisorInterface|null
    {
        if (0 === $this->items->count()) {
            return null;
        }

        if ($this->items->valid()) {
            $item = $this->items->current();

            $this->items->next();

            return $item;
        }

        $this->items->rewind();
        if (false === $loopBackToTopOfList) {
            return null;
        }

        $item = $this->items->current();

        $this->items->next();

        return $item;
    }

    public function supervise(
        Fiber $fiber,
    ): CookingSupervisorInterface {
        if ($fiber->isRunning()) {
            throw new RuntimeException(
                "Error a cooking supervisor can not supervise a running fiber, where the supervisor is included"
            );
        }

        $this->items->add($fiber);

        return $this;
    }

    public function manage(
        CookingSupervisorInterface $supervisor,
    ): CookingSupervisorInterface {
        $this->items->add($supervisor);

        return $this;
    }

    public function free(
        CookingSupervisorInterface $supervisor,
    ): CookingSupervisorInterface {
        $this->items->remove($supervisor);

        return $this;
    }

    public function rewindLoop(): CookingSupervisorInterface
    {
        $this->items->rewind();

        return $this;
    }

    /**
     * To spread the call (switch, throw, loop or finish) to the supervisor if the item is not a fiber
     * Else, resume the fiber. If the fiber is terminated, it will remove of the list
     * @param Fiber<mixed, mixed, void, mixed>|CookingSupervisorInterface $item
     * @throws Throwable
     */
    private function callOnItem(
        Fiber|CookingSupervisorInterface $item,
        Action $methodName,
        mixed $value = null,
    ): void {
        if ($item instanceof CookingSupervisorInterface) {
            if (Action::Switch === $methodName) {
                $methodName = Action::Loop;
            }

            $item->{(string) $methodName->value}($value);

            return;
        }

        if ($item->isTerminated()) {
            $this->items->remove($item);

            return;
        }

        if (!$item->isStarted()) {
            return;
        }

        if (Action::Throw === $methodName) {
            $item->throw($value);
        } elseif ($item->isSuspended()) {
            $item->resume($value);
        }

        if ($item->isTerminated()) {
            $this->items->remove($item);
        }
    }

    /**
     * @throws Throwable
     */
    public function switch(mixed $value = null): CookingSupervisorInterface
    {
        if (null !== ($item = $this->getNextItem(false))) {
            $this->callOnItem($item, Action::Switch, $value);
        }

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function throw(Throwable $value = null): CookingSupervisorInterface
    {
        if (null !== ($item = $this->getNextItem(false))) {
            $this->callOnItem($item, Action::Throw, $value);
        }

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function loop(mixed $value = null): CookingSupervisorInterface
    {
        while (null !== ($item = $this->getNextItem(false))) {
            $this->callOnItem($item, Action::Loop, $value);
        }

        return $this;
    }

    /**
     * @throws Throwable
     */
    public function finish(mixed $value = null): CookingSupervisorInterface
    {
        while (null !== ($item = $this->getNextItem(true))) {
            $this->callOnItem($item, Action::Finish, $value);
        }

        if ($this->supervisor instanceof CookingSupervisorInterface) {
            $this->supervisor->free($this);
        }

        return $this;
    }
}
