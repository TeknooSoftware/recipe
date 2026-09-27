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
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\Chef\Cooking;
use Teknoo\Recipe\Chef\Exception\MissingIngredientException;
use Teknoo\Recipe\Chef\Free;
use Teknoo\Recipe\Chef\Trained;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Dish\DishClass;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\Recipe\Draft;
use Teknoo\Recipe\Recipe\Written;
use Teknoo\Recipe\RecipeRelativePositionEnum;
use Teknoo\Tests\Recipe\Behat\IntBag;
use Throwable;

/**
 * Full cookings covering the builders of all states' closures of `Chef` and `Recipe`.
 *
 * Teknoo States shares the states instances of a stated class (and so their closures) between all its proxies
 * (`ProxyTrait::$loadedStatesCaches`): the builder of each closure runs only once per process, in the first test
 * using it, which is often a test not covering the states classes. This test resets this cache before each test,
 * so the builders run again here and their lines are attributed to the states classes.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Chef::class)]
#[CoversClass(Cooking::class)]
#[CoversClass(Free::class)]
#[CoversClass(Trained::class)]
#[CoversClass(Recipe::class)]
#[CoversClass(Draft::class)]
#[CoversClass(Written::class)]
final class StatesClosuresCoverageTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $log = [];

    protected function setUp(): void
    {
        //Internal of Teknoo States : force the proxies to reload their states and rebuild their closures in this test
        foreach ([Chef::class, Recipe::class] as $class) {
            new ReflectionProperty($class, 'loadedStatesCaches')->setValue(null, []);
        }

        $this->log = [];
    }

    private function logStep(string $name): callable
    {
        return function () use ($name): void {
            $this->log[] = $name;
        };
    }

    public function testAFullCookingWithSubRecipeJumpMergeAndDish(): void
    {
        $dish = null;
        $subRecipe = (new Recipe())->cook(
            function (ChefInterface $chef, IntBag $counter): void {
                $this->log[] = 'sub';
                IntBag::increaseValue($counter);
            },
            'sub',
        );

        $recipe = (new Recipe())
            ->require(new Ingredient('string', 'name'))
            ->require(new Ingredient(IntBag::class, 'counter'))
            ->cook(
                function (ChefInterface $chef, string $name): void {
                    $this->log[] = 'first:' . $name;
                    $chef->updateWorkPlan(['temporary' => 'to remove']);
                    $chef->merge('counter', new IntBag(10));
                    $chef->cleanWorkPlan('temporary');
                    $chef->continue([], 'third');
                },
                'first',
            )
            ->cook($this->logStep('skipped'), 'skipped')
            ->cook(
                function (ChefInterface $chef, string $temporary = 'removed'): void {
                    $this->log[] = 'third:' . $temporary;
                },
                'third',
            )
            ->execute(
                recipe: $subRecipe,
                name: 'loop',
                repeat: static function (RecipeBowl $bowl, int $counter): void {
                    if ($counter >= 2) {
                        $bowl->stopLooping();
                    }
                },
            )
            ->cook(
                $this->logStep('inserted'),
                'inserted',
                [],
                RecipeRelativePositionEnum::Before,
                'loop',
            )
            ->cook(
                function (ChefInterface $chef, IntBag $counter): void {
                    $this->log[] = 'finish';
                    $chef->finish($counter);
                },
                'finish',
            )
            ->onError($this->logStep('error'))
            ->given(
                new DishClass(
                    IntBag::class,
                    new Promise(
                        static function (IntBag $result) use (&$dish): void {
                            $dish = $result;
                        },
                        static function (Throwable $error): never {
                            throw $error;
                        },
                    ),
                ),
            );

        $chef = new Chef();
        $this->assertInstanceOf(ChefInterface::class, $chef->read($recipe));
        $this->assertInstanceOf(ChefInterface::class, $chef->process(['name' => 'foo', 'counter' => new IntBag(1)]));

        $this->assertEquals(['first:foo', 'third:removed', 'inserted', 'sub', 'sub', 'finish'], $this->log);
        //1 + 10 (merge) + 2 (sub recipe executed twice, the sub chef works on the same IntBag instance)
        $this->assertEquals(new IntBag(13), $dish);
    }

    public function testAnErrorRaisedByAStepIsHandledByTheErrorHandlers(): void
    {
        $recipe = (new Recipe())
            ->cook(
                function (ChefInterface $chef): void {
                    $this->log[] = 'raise';
                    $chef->error(new RuntimeException('manual error'));
                },
                'raise',
            )
            ->cook($this->logStep('not executed'), 'after')
            ->onError(
                function (Throwable $exception): void {
                    $this->log[] = 'handled:' . $exception->getMessage();
                },
            );

        $chef = new Chef($recipe);
        $this->assertInstanceOf(ChefInterface::class, $chef->process([]));

        $this->assertEquals(['raise', 'handled:manual error'], $this->log);
    }

    public function testAMissingIngredientStopsTheCookingBeforeTheFirstStep(): void
    {
        $recipe = (new Recipe())
            ->require(new Ingredient('string', 'need'))
            ->cook($this->logStep('not executed'), 'step');

        $chef = new Chef($recipe);

        try {
            $chef->process([]);
            $this->fail('The missing ingredient must be reported');
        } catch (MissingIngredientException $error) {
            $this->assertEquals('Error, missing some ingredients : Missing the ingredient need', $error->getMessage());
        }

        $this->assertEquals([], $this->log);
    }
}
