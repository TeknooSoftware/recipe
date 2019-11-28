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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Dish;

use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * To define Dish, instance able to check and validate the result of cooked recipe. The validation is performed on the
 * instance of the result
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
class DishClass extends AbstractDishClass
{
    private string $class;

    public function __construct(string $class, PromiseInterface $promise)
    {
        parent::__construct($promise);

        $this->class = $class;
    }

    /**
     * @inheritDoc
     */
    protected function check(&$result): bool
    {
        return \is_object($result)
            && (\is_a($result, $this->class, true) || \is_subclass_of($result, $this->class));
    }
}
