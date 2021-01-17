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

namespace Teknoo\Tests\Recipe\Cookbook;

use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
trait BaseCookbookTestTrait
{
    abstract public function buildCookbook(): CookbookInterface;

    public function testTrainWithBadChef()
    {
        $this->expectException(\TypeError::class);

        $this->buildCookbook()->train(new \stdClass());
    }

    public function testTrain()
    {
        $cookbook = $this->buildCookbook();

        self::assertInstanceOf(
            CookbookInterface::class,
            $cookbook->train($this->createMock(ChefInterface::class))
        );

        self::assertInstanceOf(
            CookbookInterface::class,
            $cookbook->train($this->createMock(ChefInterface::class))
        );
    }

    public function testPrepareWithBadWorkplan()
    {
        $this->expectException(\TypeError::class);

        $this->buildCookbook()->train(new \stdClass(), $this->createMock(ChefInterface::class));
    }

    public function testPrepareWithBadChef()
    {
        $this->expectException(\TypeError::class);

        $this->buildCookbook()->train([], $this->createMock(ChefInterface::class));
    }

    public function testPrepare()
    {
        $cookbook = $this->buildCookbook();
        $chef = $this->createMock(ChefInterface::class);

        $workplan = [];
        self::assertInstanceOf(
            CookbookInterface::class,
            $cookbook->prepare($workplan, $chef)
        );
    }

    public function testValidate()
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->buildCookbook()->validate('foo')
        );
    }

    public function testFillWithBadRecipe()
    {
        $this->expectException(\TypeError::class);

        $this->buildCookbook()->fill(new \stdClass());
    }

    public function testFillWithRecipe()
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->buildCookbook()->fill($this->createMock(RecipeInterface::class))
        );
    }
}