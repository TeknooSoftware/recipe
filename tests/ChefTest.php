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

namespace Teknoo\Tests;

use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Chef
 * @covers \Teknoo\Recipe\Chef\Cooking
 * @covers \Teknoo\Recipe\Chef\Free
 * @covers \Teknoo\Recipe\Chef\Trained
 */
class ChefTest extends AbstractChefTest
{
    public function buildChef(): ChefInterface
    {
        return new Chef();
    }

    public function testReadInConstructor()
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $recipe->expects(self::once())
            ->method('train')
            ->willReturnSelf();

        self::assertInstanceOf(
            ChefInterface::class,
            new Chef($recipe)
        );
    }
}