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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @template TSuccessArgType
 * @template TResultType
 * @template TNextSuccessArgType
 *
 * @implements PromiseInterface<TSuccessArgType, TResultType>
 */
abstract class AbstractPromise implements PromiseInterface
{
    use ImmutableTrait;

    /**
     * @var PromiseInterface<TNextSuccessArgType, TResultType>|null
     */
    private ?PromiseInterface $nextPromise = null;

    private bool $autoCallNextPromise = false;

    /**
     * @var callable|null
     */
    private $onSuccess;

    /**
     * @var callable|null
     */
    private $onFail;

    /**
     * @var TResultType
     */
    private mixed $result = null;

    private bool $called = false;

    public function __construct(
        callable $onSuccess = null,
        callable $onFail = null,
        private readonly bool $allowNext = false
    ) {
        $this->uniqueConstructorCheck();

        $this->onSuccess = $onSuccess;
        $this->onFail = $onFail;
    }

    /**
     * @param PromiseInterface<TNextSuccessArgType, TResultType>|null $promise
     */
    public function next(?PromiseInterface $promise = null, bool $autoCall = false): PromiseInterface
    {
        if (false === $this->allowNext) {
            throw new RuntimeException('Error, following promise is not allowed here');
        }

        $wrappedPromise = $promise;
        if (null !== $wrappedPromise && null !== $this->nextPromise) {
            $wrappedPromise = $this->nextPromise->next($wrappedPromise, $autoCall);
        } elseif (true === $autoCall && null !== $promise) {
            $wrappedPromise = new WrappedOneCalledPromise($promise);
        }

        $clone = clone $this;
        $clone->nextPromise = $wrappedPromise;
        $clone->autoCallNextPromise = $autoCall;

        return $clone;
    }

    /**
     * @param array<int, mixed> $args
     */
    abstract protected function processToExecution(callable $callable, array &$args): mixed;

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
            $this->result = $this->processToExecution($callable, $args);

            return;
        }

        if ($this->nextPromise instanceof PromiseInterface) {
            $args[] = $this->nextPromise;
        } else {
            //Create an empty closure to provide a void callable for callable requiring
            // a next argument
            $args[] = new static(null, null, true);
        }

        $this->result = $this->processToExecution($callable, $args);
    }

    public function success(mixed $result = null): PromiseInterface
    {
        $args = func_get_args();
        $this->call($this->onSuccess, $args);

        if ($this->nextPromise instanceof PromiseInterface && true === $this->autoCallNextPromise) {
            $this->nextPromise->success($this->result);
        }

        return $this;
    }

    public function fail(Throwable $throwable): PromiseInterface
    {
        $args = func_get_args();
        $this->call($this->onFail, $args);

        if (
            $this->nextPromise instanceof PromiseInterface
            && true === $this->autoCallNextPromise
            && ($r = $this->result ?? $throwable) instanceof Throwable
        ) {
            $this->nextPromise->fail($r);
        }

        return $this;
    }

    public function fetchResult(): mixed
    {
        if (true !== $this->called) {
            throw new RuntimeException("The promise was not be previously executted");
        }

        if ($this->nextPromise instanceof PromiseInterface) {
            return $this->nextPromise->fetchResultIfCalled($this->result) ?? $this->result;
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
