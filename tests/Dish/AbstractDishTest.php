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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\Recipe\Dish;

use PHPUnit\Framework\MockObject\MockObject;
use Teknoo\Recipe\Dish\DishInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractDishTest extends TestCase
{
    /**
     * @return DishInterface
     */
    abstract public function buildDish(): DishInterface;

    /**
     * @return MockObject|PromiseInterface
     */
    abstract protected function getPromise();

    abstract protected function getExceptedValue();

    public function testIsExceptedWithAGoodResult()
    {
        $this->getPromise()
            ->expects(self::once())
            ->method('success')
            ->with($this->getExceptedValue())
            ->willReturnSelf();

        $this->getPromise()
            ->expects(self::never())
            ->method('fail');

        self::assertInstanceOf(
            DishInterface::class,
            $this->buildDish()->isExcepted(
                $this->getExceptedValue()
            )
        );
    }

    public function testIsExceptedWithABadResult()
    {
        $this->getPromise()
            ->expects(self::never())
            ->method('success');

        $this->getPromise()
            ->expects(self::once())
            ->method('fail')
            ->willReturnSelf();

        self::assertInstanceOf(
            DishInterface::class,
            $this->buildDish()->isExcepted(
                new \stdClass()
            )
        );
    }
}
