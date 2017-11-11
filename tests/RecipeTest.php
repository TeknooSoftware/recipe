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

namespace Teknoo\Tests\Recipe;

use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Recipe
 * @covers \Teknoo\Recipe\Recipe\Draft
 * @covers \Teknoo\Recipe\Recipe\Written
 */
class RecipeTest extends AbstractRecipeTest
{
    public function buildRecipe(): RecipeInterface
    {
        return new Recipe();
    }

    public function testTrainNotEmpty()
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects(self::once())
            ->method('followSteps')
            ->with([
                'stepB' => new Bowl('microtime', []),
                'stepA' => new Bowl('microtime', []),
                'stepC' => new Bowl('microtime', [])
            ])
            ->willReturnSelf();

        $recipe = $this->buildRecipe();
        $recipe = $recipe->cook('microtime', 'stepA', [], 2);
        $recipe = $recipe->cook('microtime', 'stepB', [], 1);
        $recipe = $recipe->cook('microtime', 'stepC', [], 2);

        self::assertInstanceOf(
            RecipeInterface::class,
            $recipe->train(
                $chef
            )
        );
    }
}
