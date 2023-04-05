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
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Dish;

use PHPUnit\Framework\MockObject\MockObject;
use Teknoo\Recipe\Dish\DishInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractDishTests extends TestCase
{
    /**
     * @return DishInterface
     */
    abstract public function buildDish(): DishInterface;

    abstract protected function getPromise(): MockObject&PromiseInterface;

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
