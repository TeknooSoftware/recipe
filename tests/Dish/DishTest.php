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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\Recipe\Dish;

use PHPUnit\Framework\MockObject\MockObject;
use Teknoo\Recipe\Dish\DishClass;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Dish\AbstractDishClass
 * @covers \Teknoo\Recipe\Dish\DishClass
 */
class DishTest extends AbstractDishTest
{
    /**
     * @var PromiseInterface
     */
    private $promise;

    protected function getExceptedValue()
    {
        return new \DateTime('2018-01-01');
    }

    /**
     * @return MockObject|PromiseInterface
     */
    protected function getPromise()
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
