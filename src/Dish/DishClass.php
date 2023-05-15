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

namespace Teknoo\Recipe\Dish;

use Teknoo\Recipe\Promise\PromiseInterface;

use function is_a;
use function is_object;

/**
 * To define Dish, instance able to check and validate the result of cooked recipe. The validation is performed on the
 * instance of the result
 *
 * @see DishInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DishClass extends AbstractDishClass
{
    public function __construct(
        private readonly string $class,
        PromiseInterface $promise
    ) {
        parent::__construct($promise);
    }

    protected function check(mixed &$result): bool
    {
        return is_object($result)
            && (is_a($result, $this->class, true));
    }
}
