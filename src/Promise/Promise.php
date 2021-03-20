<?php

/*
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

    /**
     * @var callable|null
     */
    private $onSuccess;

    /**
     * @var callable|null
     */
    private $onFail;

    public function __construct(callable $onSuccess = null, callable $onFail = null)
    {
        $this->onSuccess = $onSuccess;
        $this->onFail = $onFail;

        $this->uniqueConstructorCheck();
    }

    public function success(mixed $result = null): PromiseInterface
    {
        if (is_callable($this->onSuccess)) {
            ($this->onSuccess)(...func_get_args());
        }

        return $this;
    }

    public function fail(Throwable $throwable): PromiseInterface
    {
        if (is_callable($this->onFail)) {
            ($this->onFail)(...func_get_args());
        }

        return $this;
    }
}
