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

use Fiber;

/**
 * Default implementation of PromiseInterface, executing success or fail callback into
 * a fiber. The fiber instance will not passed to the callable.
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
 * @extends  AbstractPromise<TSuccessArgType, TResultType, TNextSuccessArgType>
 */
class FiberPromise extends AbstractPromise
{
    protected function processToExecution(callable $callable, array &$args): mixed
    {
        $fiber = new Fiber($callable);
        $fiber->start(...$args);
        return $fiber->getReturn();
    }
}
