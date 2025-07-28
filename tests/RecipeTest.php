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

namespace Teknoo\Tests\Recipe;

use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\Recipe\Draft;
use Teknoo\Recipe\Recipe\Written;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Recipe::class)]
#[CoversClass(Draft::class)]
#[CoversClass(Written::class)]
final class RecipeTest extends AbstractRecipeTests
{
    public function buildRecipe(): RecipeInterface
    {
        return new Recipe();
    }
    public function testTrainNotEmpty(): void
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects($this->once())
            ->method('followSteps')
            ->with([
                'stepB' => new Bowl('microtime', [], 'stepB'),
                'stepA' => new Bowl('microtime', [], 'stepA'),
                'stepA1' => new Bowl('microtime', [], 'stepA'),
                'stepC' => new Bowl('microtime', [], 'stepC')
            ])
            ->willReturnSelf();

        $recipe = $this->buildRecipe();
        $recipe = $recipe->cook('microtime', 'stepA', [], 2);
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
