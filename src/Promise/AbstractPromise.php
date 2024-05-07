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

namespace Teknoo\Recipe\Promise;

use SensitiveParameter;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Promise\Exception\NotExecutedPromiseException;
use Teknoo\Recipe\Promise\Exception\NotGrantedPromiseException;
use Throwable;

use function func_get_args;
use function is_callable;

/**
 * Default implementation of PromiseInterface;
 *
 * @see PromiseInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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

    /**
     * @var TResultType|(callable(): TResultType)|null
     */
    private mixed $defaultResult = null;

    private bool $called = false;

    private bool $isFailing = false;

    public function __construct(
        callable $onSuccess = null,
        callable $onFail = null,
        private readonly bool $allowNext = true,
        private readonly bool $callOnFailOnException = true,
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
            throw new NotGrantedPromiseException('Error, following promise is not allowed here');
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
    abstract protected function processToExecution(callable &$callable, array &$args): mixed;

    /**
     * @param array<int, mixed> $args
     * @throws Throwable
     */
    private function call(?callable &$callable, array &$args): void
    {
        $this->called = true;

        if (!is_callable($callable)) {
            return;
        }

        if ($this->allowNext) {
            if ($this->nextPromise instanceof PromiseInterface) {
                $args[] = $this->nextPromise;
            } else {
                //Create an empty closure to provide a void callable for callable requiring
                // a next argument
                $args[] = new static(null, null, true);
            }
        }

        try {
            $this->result = $this->processToExecution($callable, $args);
        } catch (Throwable $error) {
            if (
                !$this->isFailing
                && $this->callOnFailOnException
                && is_callable($this->onFail)
            ) {
                $this->isFailing = true;
                $this->fail($error);
                $this->isFailing = false;
            } else {
                throw $error;
            }
        }
    }

    public function __invoke(...$args): mixed
    {
        return $this->success(...$args)
            ->fetchResult();
    }

    public function success(...$args): PromiseInterface
    {
        $this->call($this->onSuccess, $args);

        if ($this->nextPromise instanceof PromiseInterface && true === $this->autoCallNextPromise) {
            $this->nextPromise->success($this->result);
        }

        return $this;
    }

    public function fail(#[SensitiveParameter] Throwable $throwable): PromiseInterface
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

    public function setDefaultResult(mixed $default): PromiseInterface
    {
        $this->defaultResult = $default;

        return $this;
    }

    public function fetchResult(mixed $default = null): mixed
    {
        if ($this->called) {
            return $this->fetchResultIfCalled();
        }

        $default ??= $this->defaultResult;

        if (is_callable($default)) {
            return $default();
        }

        return $default;
    }

    public function fetchResultIfCalled(): mixed
    {
        if (true !== $this->called) {
            throw new NotExecutedPromiseException("The promise was not be previously executed");
        }

        if ($this->nextPromise instanceof PromiseInterface) {
            return $this->nextPromise->fetchResult($this->result) ?? $this->result;
        }

        return $this->result;
    }
}
