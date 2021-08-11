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

use Teknoo\Immutable\ImmutableInterface;
use Throwable;

/**
 * PromiseInterface is a contract to create to allow an actor, following east,
 * to call the actor without perform a return or an assignment and without know the interface / class of the next
 * objects. Promise must be immutable
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface PromiseInterface extends ImmutableInterface
{
    /*
     * To define a new promise to pass to the called callback.
     */
    public function next(?PromiseInterface $promise = null): PromiseInterface;

    /*
    * To call the callback defined when the actor has successfully it's operation.
    */
    public function success(mixed $result = null): PromiseInterface;

    /*
     * To call the callback defined when an error has been occurred.
     */
    public function fail(Throwable $throwable): PromiseInterface;
}
