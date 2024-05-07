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
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template TSuccessArgType
 * @template TResultType
 *
 * @implements   PromiseInterface<TSuccessArgType, TResultType>
 */
class WrappedOneCalledPromise implements PromiseInterface
{
    use ImmutableTrait;

    private bool $called = false;

    private bool $failed = false;

    /**
     * @param PromiseInterface<TSuccessArgType, TResultType> $promise
     */
    public function __construct(
        private PromiseInterface $promise,
    ) {
    }

    public function next(?PromiseInterface $promise = null, bool $autoCall = false): PromiseInterface
    {
        $clone = clone $this;
        $clone->promise = $this->promise->next($promise, $autoCall);
        return $clone;
    }

    public function __invoke(...$args): mixed
    {
        return $this->success(...$args)->fetchResult();
    }

    public function success(...$args): PromiseInterface
    {
        if (!$this->called) {
            $this->called = true;
            $this->promise->success(...$args);
        }

        return $this;
    }

    public function fail(#[SensitiveParameter] Throwable $throwable): PromiseInterface
    {
        if (!$this->failed) {
            $this->called = true;
            $this->failed = true;
            $this->promise->fail($throwable);
        }

        return $this;
    }

    public function setDefaultResult(mixed $default): PromiseInterface
    {
        $this->promise->setDefaultResult($default);

        return $this;
    }

    public function fetchResult(mixed $default = null): mixed
    {
        return $this->promise->fetchResult($default);
    }

    public function fetchResultIfCalled(): mixed
    {
        return $this->promise->fetchResultIfCalled();
    }
}
