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
use ReflectionProperty;
use RuntimeException;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\Chef\Cooking;
use Teknoo\Recipe\Chef\Exception\MissingIngredientException;
use Teknoo\Recipe\Chef\Free;
use Teknoo\Recipe\Chef\Trained;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Recipe;

/**
 * Regression tests about the chef life cycle between two cookings.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Chef::class)]
#[CoversClass(Trained::class)]
#[CoversClass(Free::class)]
#[CoversClass(Cooking::class)]
final class ChefRegressionTest extends TestCase
{
    public function testWorkPlanIsCleanedWhenAnExceptionEscapesProcess(): void
    {
        $seen = [];
        $recipe = (new Recipe())->cook(
            function (ChefInterface $chef, string $secret = 'none') use (&$seen): void {
                $seen[] = $secret;

                throw new RuntimeException('step failed');
            },
            'failing',
        );

        $chef = new Chef($recipe);

        try {
            $chef->process(['secret' => 's3cr3t']);
            $this->fail('The exception must escape from the first cooking');
        } catch (RuntimeException $error) {
            $this->assertEquals('step failed', $error->getMessage());
        }

        try {
            $chef->process([]);
            $this->fail('The exception must escape from the second cooking');
        } catch (RuntimeException $error) {
            $this->assertEquals('step failed', $error->getMessage());
        }

        $this->assertEquals(['s3cr3t', 'none'], $seen, 'The secret must not leak into the second cooking');
    }

    public function testASecondCookingWithoutTheFailingIngredientSucceeds(): void
    {
        $log = [];
        $recipe = (new Recipe())
            ->cook(
                function (ChefInterface $chef, bool $boom = false) use (&$log): void {
                    $log[] = 'step1';

                    if ($boom) {
                        throw new RuntimeException('boom');
                    }
                },
                'step1',
            )
            ->cook(
                function () use (&$log): void {
                    $log[] = 'step2';
                },
                'step2',
            );

        $chef = new Chef($recipe);

        try {
            $chef->process(['boom' => true]);
            $this->fail('The exception must escape from the first cooking');
        } catch (RuntimeException $error) {
            $this->assertEquals('boom', $error->getMessage());
        }

        $this->assertInstanceOf(ChefInterface::class, $chef->process([]));
        $this->assertEquals(['step1', 'step1', 'step2'], $log);
    }

    public function testMissingIngredientsAreResetBetweenTwoCookings(): void
    {
        $seen = [];
        $recipe = (new Recipe())
            ->require(new Ingredient('string', 'need'))
            ->cook(
                function (string $need) use (&$seen): void {
                    $seen[] = $need;
                },
                'step',
            );

        $chef = new Chef($recipe);

        try {
            $chef->process([]);
            $this->fail('The missing ingredient must be reported');
        } catch (MissingIngredientException $error) {
            $this->assertStringContainsString('need', $error->getMessage());
        }

        $this->assertInstanceOf(ChefInterface::class, $chef->process(['need' => 'ok']));
        $this->assertEquals(['ok'], $seen);
    }

    public function testContinueToStepNamedZero(): void
    {
        $log = [];
        $recipe = (new Recipe())
            ->cook(
                function (ChefInterface $chef) use (&$log): void {
                    $log[] = 'a';
                    $chef->continue([], '0');
                },
                'a',
            )
            ->cook(
                function () use (&$log): void {
                    $log[] = 'skipped';
                },
                'skipped',
            )
            ->cook(
                function () use (&$log): void {
                    $log[] = '0';
                },
                '0',
            );

        (new Chef($recipe))->process([]);

        $this->assertEquals(['a', '0'], $log);
    }

    public function testCloneTakenDuringAStepCanCookIndependently(): void
    {
        $log = [];
        $clone = null;
        $recipe = (new Recipe())
            ->cook(
                function (ChefInterface $chef, bool $cloned = false) use (&$log, &$clone): void {
                    $log[] = 'step1:' . var_export($cloned, true);

                    if (!$cloned) {
                        $clone = clone $chef;
                        $clone->process(['cloned' => true]);
                    }
                },
                'step1',
            )
            ->cook(
                function (bool $cloned = false) use (&$log): void {
                    $log[] = 'step2:' . var_export($cloned, true);
                },
                'step2',
            );

        $chef = new Chef($recipe);
        $chef->process([]);

        $this->assertInstanceOf(Chef::class, $clone);
        $this->assertNotSame($chef, $clone);
        $this->assertEquals(['step1:false', 'step1:true', 'step2:true', 'step2:false'], $log);
    }

    public function testCloneResetsProxyCallersStack(): void
    {
        //The callers stack is an internal of the states proxy (teknoo/states) : a clone taken during a cooking must
        //not inherit the stack of its original, else all its next callers are granted to call its private methods
        $clone = null;
        $recipe = (new Recipe())->cook(
            function (ChefInterface $chef) use (&$clone): void {
                $clone = clone $chef;
            },
            'step',
        );

        (new Chef($recipe))->process([]);

        $this->assertInstanceOf(Chef::class, $clone);
        $callersStack = new ReflectionProperty(Chef::class, 'callersStack');
        $this->assertEquals([], $callersStack->getValue($clone));
    }
}
