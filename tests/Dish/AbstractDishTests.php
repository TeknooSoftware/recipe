<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Dish;

use stdClass;
use PHPUnit\Framework\MockObject\MockObject;
use Teknoo\Recipe\Dish\DishInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
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

    public function testIsExceptedWithAGoodResult(): void
    {
        $this->getPromise()
            ->expects($this->once())
            ->method('success')
            ->with($this->getExceptedValue())
            ->willReturnSelf();

        $this->getPromise()
            ->expects($this->never())
            ->method('fail');

        self::assertInstanceOf(
            DishInterface::class,
            $this->buildDish()->isExcepted(
                $this->getExceptedValue()
            )
        );
    }

    public function testIsExceptedWithABadResult(): void
    {
        $this->getPromise()
            ->expects($this->never())
            ->method('success');

        $this->getPromise()
            ->expects($this->once())
            ->method('fail')
            ->willReturnSelf();

        self::assertInstanceOf(
            DishInterface::class,
            $this->buildDish()->isExcepted(
                new stdClass()
            )
        );
    }
}
