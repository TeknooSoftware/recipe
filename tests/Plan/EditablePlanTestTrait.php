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

namespace Teknoo\Tests\Recipe\Plan;

use TypeError;
use stdClass;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\Step;
use Teknoo\Recipe\PlanInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
trait EditablePlanTestTrait
{
    use BasePlanTestTrait;

    abstract public function buildPlan(): EditablePlanInterface;

    public function testAddBadPriority(): void
    {
        $this->expectException(TypeError::class);
        $this->buildPlan()->add(fn (): true => true, new stdClass(), );
    }

    public function testAddBadStep(): void
    {
        $this->expectException(TypeError::class);
        $this->buildPlan()->add(new stdClass(), 1);
    }

    public function testAddWithBowl(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->buildPlan()->add($this->createMock(BowlInterface::class), 1),
        );
    }

    public function testAddWithStep(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->buildPlan()->add($this->createMock(Step::class), 1),
        );
    }

    public function testAddWithCallable(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->buildPlan()->add(fn (): true => true, 1),
        );
    }

    public function testAddErrorHandlerWithBadCallable(): void
    {
        $this->expectException(TypeError::class);
        $this->buildPlan()->addErrorHandler(new stdClass());
    }

    public function testAddErrorHandler(): void
    {
        self::assertInstanceOf(
            EditablePlanInterface::class,
            $this->buildPlan()->addErrorHandler(fn (): true => true),
        );
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
}
