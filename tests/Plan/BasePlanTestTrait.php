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
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Plan;

use TypeError;
use stdClass;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\PlanInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait BasePlanTestTrait
{
    abstract public function buildPlan(): PlanInterface;

    public function testTrainWithBadChef(): void
    {
        $this->expectException(TypeError::class);

        $this->buildPlan()->train(new stdClass());
    }

    public function testTrain(): void
    {
        $plan = $this->buildPlan();

        self::assertInstanceOf(
            PlanInterface::class,
            $plan->train($this->createMock(ChefInterface::class))
        );

        self::assertInstanceOf(
            PlanInterface::class,
            $plan->train($this->createMock(ChefInterface::class))
        );
    }

    public function testPrepareWithBadWorkplan(): void
    {
        $this->expectException(TypeError::class);

        $this->buildPlan()->train(new stdClass(), $this->createMock(ChefInterface::class));
    }

    public function testPrepareWithBadChef(): void
    {
        $this->expectException(TypeError::class);

        $this->buildPlan()->train([], $this->createMock(ChefInterface::class));
    }

    public function testPrepare(): void
    {
        $plan = $this->buildPlan();
        $chef = $this->createMock(ChefInterface::class);

        $workplan = [];
        self::assertInstanceOf(
            PlanInterface::class,
            $plan->prepare($workplan, $chef)
        );
    }

    public function testValidate(): void
    {
        self::assertInstanceOf(
            PlanInterface::class,
            $this->buildPlan()->validate('foo')
        );
    }

    public function testFillWithBadRecipe(): void
    {
        $this->expectException(TypeError::class);

        $this->buildPlan()->fill(new stdClass());
    }

    public function testFillWithRecipe(): void
    {
        self::assertInstanceOf(
            PlanInterface::class,
            $this->buildPlan()->fill($this->createMock(RecipeInterface::class))
        );
    }

    public function testAddToWorkplanWithBadName(): void
    {
        $this->expectException(TypeError::class);

        $this->buildPlan()->addToWorkplan(new stdClass(), 'foo');
    }

    public function testAddToWorkplanWithRecipe(): void
    {
        self::assertInstanceOf(
            PlanInterface::class,
            $this->buildPlan()->addToWorkplan(
                'foo',
                new stdClass()
            )
        );
    }
}
