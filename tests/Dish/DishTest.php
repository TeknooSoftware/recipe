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

namespace Teknoo\Tests\Recipe\Dish;

use PHPUnit\Framework\MockObject\MockObject;
use Teknoo\Recipe\Dish\DishClass;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Dish\AbstractDishClass
 * @covers \Teknoo\Recipe\Dish\DishClass
 */
class DishTest extends AbstractDishTests
{
    /**
     * @var PromiseInterface
     */
    private $promise;

    protected function getExceptedValue()
    {
        return new \DateTime('2018-01-01');
    }

    protected function getPromise(): MockObject&PromiseInterface
    {
        if (!$this->promise instanceof PromiseInterface) {
            $this->promise = $this->createMock(PromiseInterface::class);
        }

        return $this->promise;
    }

    /**
     * @inheritDoc
     */
    public function buildDish(): DishInterface
    {
        return new DishClass(
            \DateTime::class,
            $this->getPromise()
        );
    }
}
