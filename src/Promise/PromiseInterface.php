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

namespace Teknoo\Recipe\Promise;

use SensitiveParameter;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Recipe\Promise\Exception\AlreadyCalledPromiseException;
use Throwable;

/**
 * PromiseInterface is a contract to create to allow an actor, following east,
 * to call the actor without perform a return or an assignment and without know the interface / class of the next
 * objects. Promise must be immutable
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @template-covariant TSuccessArgType
 * @template-covariant TResultType
 */
interface PromiseInterface extends ImmutableInterface
{
    /**
     * To define a new promise to pass to the called callback.
     * If $autoCall is set to true, the promise is automatically called with the result
     * of the current promise
     *
     * @param PromiseInterface<TSuccessArgType, TResultType>|null $promise
     * @param bool $autoCall to autocall the next promise at end of this promise
     * @return PromiseInterface<TSuccessArgType, TResultType>
     */
    public function next(?PromiseInterface $promise = null, bool $autoCall = true): PromiseInterface;

    /**
     * To call the callback defined when the actor has successfully it's operation.
     *
     * @param ?TSuccessArgType ...$args
     * @return PromiseInterface<TSuccessArgType, TResultType>
     * @throws AlreadyCalledPromiseException
     */
    public function success(...$args): PromiseInterface;

    /**
     * To use promise with methods or functions requiring a callable
     * Will return value returned by the success callback, like with `fetchResult`
     *
     * @param TSuccessArgType ...$args
     * @return TResultType|null
     */
    public function __invoke(...$args): mixed;

    /**
     * To call the callback defined when an error has been occurred.
     * @return PromiseInterface<TSuccessArgType, TResultType>
     * @throws AlreadyCalledPromiseException
     */
    public function fail(#[SensitiveParameter] Throwable $throwable): PromiseInterface;

    /**
     * To set the default value to return when `fetchResult` without default value.
     * The default value can be a callable, it will be called automatically.
     *
     * The default value is returned if the success callable is not called, if the success callable return a null value,
     * the null value will be returned
     *
     * @param TResultType|(callable(): TResultType)|null $default
     * @return PromiseInterface<TSuccessArgType, TResultType>
     */
    public function setDefaultResult(mixed $default): PromiseInterface;

    /**
     * To get the returned value by the callback on the promise (Can be null if the callback return nothing).
     * (Not "east compliant", but useful to integrate east code with an non-east code).
     * If the promise was not called, the method will return $default value.
     *
     * @internal
     *
     * @param callable|TResultType|null $default
     * @return null|TResultType
     */
    public function fetchResult(mixed $default = null): mixed;

    /**
     * To get the returned value by the callback on the promise (Can be null if the callback return nothing).
     * (Not east compliant, but useful to integrate east code with an non-east code).
     * If the promise was not called, the method will throw an exception.
     *
     * @return null|TResultType
     */
    public function fetchResultIfCalled(): mixed;

    /**
     * To reset the promise instance to allow to be resued
     * @return PromiseInterface<TSuccessArgType, TResultType>
     */
    public function reset(): PromiseInterface;
}
