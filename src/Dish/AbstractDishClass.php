<?php

declare(strict_types=1);

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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Recipe\Dish;

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Base class dish
 *
 * @see DishInterface
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractDishClass implements DishInterface
{
    use ImmutableTrait;

    private PromiseInterface $promise;

    public function __construct(PromiseInterface $promise)
    {
        $this->uniqueConstructorCheck();

        $this->promise = $promise;
    }

    /**
     * To define in final class to check the result of the cooked recipe.
     *
     * @param mixed &$result
     *
     * @return bool
     */
    abstract protected function check(&$result): bool;

    /**
     * @inheritDoc
     */
    public function isExcepted($result): DishInterface
    {
        if ($this->check($result)) {
            $this->promise->success($result);
        } else {
            $this->promise->fail(new \RuntimeException('Dish is not accepted'));
        }

        return $this;
    }
}
