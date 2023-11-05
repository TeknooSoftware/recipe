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

namespace Teknoo\Tests\Recipe\Cookbook;

use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
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

    public function testAddToWorkplanWithBadName()
    {
        $this->expectException(\TypeError::class);

        $this->buildCookbook()->addToWorkplan(new \stdClass(), 'foo');
    }

    public function testAddToWorkplanWithRecipe()
    {
        self::assertInstanceOf(
            CookbookInterface::class,
            $this->buildCookbook()->addToWorkplan(
                'foo',
                new \stdClass()
            )
        );
    }
}
