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

use Teknoo\Immutable\ImmutableInterface;
use Throwable;

/**
 * PromiseInterface is a contract to create to allow an actor, following east,
 * to call the actor without perform a return or an assignment and without know the interface / class of the next
 * objects. Promise must be immutable
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
 */
interface PromiseInterface extends ImmutableInterface
{
    /**
     * To define a new promise to pass to the called callback.
     *
     * @param PromiseInterface<mixed, TResultType>|null $promise
     * @return PromiseInterface<TSuccessArgType, TResultType>
     */
    public function next(?PromiseInterface $promise = null): PromiseInterface;

    /**
     * To call the callback defined when the actor has successfully it's operation.
     *
     * @param TSuccessArgType|null $result
     * @return PromiseInterface<TSuccessArgType, TResultType>
     */
    public function success(mixed $result = null): PromiseInterface;

    /**
     * To call the callback defined when an error has been occurred.
     * @return PromiseInterface<TSuccessArgType, TResultType>
     */
    public function fail(Throwable $throwable): PromiseInterface;

    /**
     * To get the returned value by the callback on the promise (Can be null if the callback return nothing).
     * (Not east compliant, but useful to integrate east code with an non-east code).
     * If the promise was not called, the method will throw an exception.
     *
     * @return TResultType
     */
    public function fetchResult(): mixed;

    /**
     * To get the returned value by the callback on the promise (Can be null if the callback return nothing).
     * (Not east compliant, but useful to integrate east code with an non-east code).
     * If the promise was not called, the method will return $default value.
     *
     * @internal
     *
     * @param TResultType $default
     * @return TResultType
     */
    public function fetchResultIfCalled(mixed $default): mixed;
}
