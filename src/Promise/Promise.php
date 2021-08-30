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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Promise;

use RuntimeException;
use Teknoo\Immutable\ImmutableTrait;
use Throwable;

use function func_get_args;
use function is_callable;

/**
 * Default implementation of PromiseInterface;
 *
 * @see PromiseInterface
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class Promise implements PromiseInterface
{
    use ImmutableTrait;

    private ?PromiseInterface $nextPromise = null;

    /**
     * @var callable|null
     */
    private $onSuccess;

    /**
     * @var callable|null
     */
    private $onFail;

    private bool $allowNext = false;

    private mixed $result = null;

    private bool $called = false;

    public function __construct(callable $onSuccess = null, callable $onFail = null, bool $allowNext = false)
    {
        $this->uniqueConstructorCheck();

        $this->onSuccess = $onSuccess;
        $this->onFail = $onFail;
        $this->allowNext = $allowNext;
    }

    public function next(?PromiseInterface $promise = null): PromiseInterface
    {
        if (false === $this->allowNext) {
            throw new RuntimeException('Error, following promise is not allowed here');
        }

        $clone = clone $this;
        $clone->nextPromise = $promise;

        return $clone;
    }

    /**
     * @param array<int, mixed> $args
     */
    private function call(?callable $callable, array &$args): void
    {
        $this->called = true;

        if (!is_callable($callable)) {
            return;
        }

        if (!$this->allowNext) {
            $this->result = $callable(...$args);

            return;
        }

        if ($this->nextPromise instanceof PromiseInterface) {
            $args[] = $this->nextPromise;
        } else {
            //Create an empty closure to provide a void callable for callable requiring
            // a next argument
            $args[] = new static(null, null, true);
        }

        $this->result = $callable(...$args);
    }

    public function success(mixed $result = null): PromiseInterface
    {
        $args = func_get_args();
        $this->call($this->onSuccess, $args);

        return $this;
    }

    public function fail(Throwable $throwable): PromiseInterface
    {
        $args = func_get_args();
        $this->call($this->onFail, $args);

        return $this;
    }

    public function fetchResult(): mixed
    {
        if (true !== $this->called) {
            throw new RuntimeException("The promise was not be previously executted");
        }

        if (
            empty($this->result)
            && $this->nextPromise instanceof PromiseInterface
        ) {
            return $this->nextPromise->fetchResultIfCalled($this->result);
        }

        return $this->result;
    }

    public function fetchResultIfCalled(mixed $default): mixed
    {
        if (true !== $this->called) {
            return $default;
        }

        return $this->fetchResult();
    }
}
