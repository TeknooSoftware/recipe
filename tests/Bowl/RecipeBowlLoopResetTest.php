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

namespace Teknoo\Tests\Recipe\Bowl;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\AbstractRecipeBowl;
use Teknoo\Recipe\Bowl\FiberRecipeBowl;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\Recipe;

/**
 * Regression tests about the reuse of a recipe bowl (sub recipe) between two cookings.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AbstractRecipeBowl::class)]
#[CoversClass(RecipeBowl::class)]
#[CoversClass(FiberRecipeBowl::class)]
final class RecipeBowlLoopResetTest extends TestCase
{
    /**
     * @return string[]
     */
    private function cookTwice(bool $inFiber): array
    {
        $log = [];
        $subRecipe = (new Recipe())->cook(
            function () use (&$log): void {
                $log[] = 's';
            },
            'sub',
        );

        $recipe = (new Recipe())
            ->execute(
                recipe: $subRecipe,
                name: 'loop',
                repeat: function (AbstractRecipeBowl $bowl, int $counter): void {
                    if ($counter >= 3) {
                        $bowl->stopLooping();
                    }
                },
                inFiber: $inFiber,
            )
            ->cook(
                function () use (&$log): void {
                    $log[] = 'end';
                },
                'end',
            );

        $chef = new Chef($recipe);
        $chef->process([]);
        $chef->process([]);

        return $log;
    }

    public function testStopLoopingIsResetBetweenTwoExecutions(): void
    {
        $this->assertEquals(
            ['s', 's', 's', 'end', 's', 's', 's', 'end'],
            $this->cookTwice(false),
        );
    }

    public function testStopLoopingIsResetBetweenTwoExecutionsInFiber(): void
    {
        $this->assertEquals(
            ['s', 's', 's', 'end', 's', 's', 's', 'end'],
            $this->cookTwice(true),
        );
    }
}
