<?php

declare(strict_types=1);

/**
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Recipe\Promise;

use Teknoo\Immutable\ImmutableInterface;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface PromiseInterface extends ImmutableInterface
{
     /**
     * To call the callback defined when the actor has successfully it's operation.
     *
     * @param mixed|null $result
     *
     * @return PromiseInterface
     */
    public function success($result = null): PromiseInterface;

    /**
     *To call the callback defined when an error has been occurred.
     *
     * @param \Throwable $throwable
     *
     * @return PromiseInterface
     */
    public function fail(\Throwable $throwable): PromiseInterface;
}
