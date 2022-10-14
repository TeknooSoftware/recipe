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

use Teknoo\Immutable\ImmutableTrait;
use Throwable;

/**
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
 *
 * @implements   PromiseInterface<TSuccessArgType, TResultType>
 */
class WrappedOneCalledPromise implements PromiseInterface
{
    use ImmutableTrait;

    private bool $called = false;

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

    public function success(mixed $result = null): PromiseInterface
    {
        if (!$this->called) {
            $this->called = true;
            $this->promise->success(...(func_get_args()));
        }

        return $this;
    }

    public function fail(Throwable $throwable): PromiseInterface
    {
        $this->promise->fail($throwable);

        return $this;
    }

    public function fetchResult(): mixed
    {
        return $this->promise->fetchResult();
    }

    public function fetchResultIfCalled(mixed $default): mixed
    {
        return $this->promise->fetchResultIfCalled($default);
    }
}
