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

namespace Teknoo\Recipe\Dish;

use RuntimeException;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * Base class dish
 *
 * @see DishInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractDishClass implements DishInterface
{
    use ImmutableTrait;

    /**
     * @param PromiseInterface<mixed, mixed> $promise
     */
    public function __construct(
        private readonly PromiseInterface $promise
    ) {
        $this->uniqueConstructorCheck();
    }

    /*
     * To define in final class to check the result of the cooked recipe.
     */
    abstract protected function check(mixed &$result): bool;

    public function isExcepted(mixed $result): DishInterface
    {
        if ($this->check($result)) {
            $this->promise->success($result);
        } else {
            $this->promise->fail(new RuntimeException('Dish is not accepted'));
        }

        return $this;
    }
}
