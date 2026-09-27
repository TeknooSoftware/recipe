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
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\Recipe\Draft;
use Teknoo\Recipe\Recipe\Written;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Recipe\RecipeRelativePositionEnum;

/**
 * Regression tests about the ordering of steps in a recipe when a position is defined.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Recipe::class)]
#[CoversClass(Draft::class)]
#[CoversClass(Written::class)]
final class RecipeStepPositionTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $log = [];

    private function logStep(string $name): callable
    {
        return function () use ($name): void {
            $this->log[] = $name;
        };
    }

    private function subRecipe(string $name): RecipeInterface
    {
        return (new Recipe())->cook($this->logStep($name), $name);
    }

    /**
     * @return string[]
     */
    private function cook(RecipeInterface $recipe): array
    {
        $this->log = [];
        (new Chef($recipe))->process([]);

        return $this->log;
    }

    public function testCookAtPositionZeroIsPlacedFirst(): void
    {
        $recipe = (new Recipe())
            ->cook($this->logStep('a'), 'a', [], 10)
            ->cook($this->logStep('b'), 'b', [], 20)
            ->cook($this->logStep('zero'), 'zero', [], 0);

        $this->assertEquals(['zero', 'a', 'b'], $this->cook($recipe));
    }

    public function testExecuteAtPositionZeroIsPlacedFirst(): void
    {
        $recipe = (new Recipe())
            ->cook($this->logStep('a'), 'a', [], 10)
            ->cook($this->logStep('b'), 'b', [], 20)
            ->execute($this->subRecipe('zero'), 'zero', 1, 0);

        $this->assertEquals(['zero', 'a', 'b'], $this->cook($recipe));
    }

    private function recipeWithExplicitPositions(): RecipeInterface
    {
        return (new Recipe())
            ->cook($this->logStep('a'), 'a', [], 10)
            ->cook($this->logStep('b'), 'b', [], 20)
            ->cook($this->logStep('c'), 'c', [], 30);
    }

    public function testCookAfterOffsetWithExplicitPositionsKeepsOrder(): void
    {
        $recipe = $this->recipeWithExplicitPositions()->cook(
            action: $this->logStep('new'),
            name: 'new',
            position: RecipeRelativePositionEnum::After,
            offsetStepName: 'b',
        );

        $this->assertEquals(['a', 'b', 'new', 'c'], $this->cook($recipe));
    }

    public function testCookBeforeOffsetWithExplicitPositions(): void
    {
        $recipe = $this->recipeWithExplicitPositions()->cook(
            action: $this->logStep('new'),
            name: 'new',
            position: RecipeRelativePositionEnum::Before,
            offsetStepName: 'b',
        );

        $this->assertEquals(['a', 'new', 'b', 'c'], $this->cook($recipe));
    }

    public function testCookBeforeTheFirstStepWithExplicitPositions(): void
    {
        $recipe = $this->recipeWithExplicitPositions()->cook(
            action: $this->logStep('new'),
            name: 'new',
            position: RecipeRelativePositionEnum::Before,
            offsetStepName: 'a',
        );

        $this->assertEquals(['new', 'a', 'b', 'c'], $this->cook($recipe));
    }

    public function testCookAfterUnknownOffsetAppendsAtEnd(): void
    {
        $recipe = $this->recipeWithExplicitPositions()->cook(
            action: $this->logStep('new'),
            name: 'new',
            position: RecipeRelativePositionEnum::After,
            offsetStepName: 'unknown',
        );

        $this->assertEquals(['a', 'b', 'c', 'new'], $this->cook($recipe));
    }

    public function testExecuteAfterOffsetWithExplicitPositions(): void
    {
        $recipe = $this->recipeWithExplicitPositions()->execute(
            recipe: $this->subRecipe('sub'),
            name: 'sub',
            position: RecipeRelativePositionEnum::After,
            offsetStepName: 'b',
        );

        $this->assertEquals(['a', 'b', 'sub', 'c'], $this->cook($recipe));
    }

    public function testExecuteBeforeUnknownOffsetAppendsAtEnd(): void
    {
        $recipe = $this->recipeWithExplicitPositions()->execute(
            recipe: $this->subRecipe('sub'),
            name: 'sub',
            position: RecipeRelativePositionEnum::Before,
            offsetStepName: 'unknown',
        );

        $this->assertEquals(['a', 'b', 'c', 'sub'], $this->cook($recipe));
    }
}
