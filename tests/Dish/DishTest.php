<?php

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

namespace Teknoo\Tests\Recipe\Dish;

use Teknoo\Recipe\Dish\DishClass;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @covers \Teknoo\Recipe\Dish\AbstractDishClass
 * @covers \Teknoo\Recipe\Dish\DishClass
 */
class DishTest extends AbstractDishTest
{
    protected function getExceptedValue()
    {
        return 'fooBar';
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PromiseInterface
     */
    protected function getPromise()
    {
        return $this->createMock(PromiseInterface::class);
    }

    /**
     * @inheritDoc
     */
    public function buildDish(): DishInterface
    {
        return new DishClass(
            $this->getExceptedValue(),
            $this->getPromise()
        );
    }
}