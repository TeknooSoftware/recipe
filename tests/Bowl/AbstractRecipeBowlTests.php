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

use TypeError;
use stdClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\AbstractRecipeBowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractRecipeBowlTests extends TestCase
{
    /**
     * @param RecipeInterface $recipe
     * @param int $repeat
     * @return RecipeBowl
     */
    abstract public function buildBowl($recipe, $repeat): AbstractRecipeBowl;

    public function testExceptionOnBadRecipe(): void
    {
        $this->expectException(TypeError::class);
        $this->buildBowl(new stdClass());
    }

    public function testExceptionOnExecuteWithBadChef(): void
    {
        $this->expectException(TypeError::class);
        $values = ['foo' => 'bar'];
        $this->buildBowl($this->createMock(RecipeInterface::class), 1)
            ->execute(
                new stdClass(),
                $values,
                $this->createMock(CookingSupervisorInterface::class),
            );
    }

    public function testExceptionOnExecuteWithBadWorkPlan(): void
    {
        $this->expectException(TypeError::class);
        $values = new stdClass();
        $this->buildBowl($this->createMock(RecipeInterface::class), 1)
            ->execute(
                $this->createMock(ChefInterface::class),
                $values,
                $this->createMock(CookingSupervisorInterface::class),
            );
    }

    public function testExceptionOnExecuteWithBadSupervisor(): void
    {
        $this->expectException(TypeError::class);
        $values = [];
        $this->buildBowl($this->createMock(RecipeInterface::class), 1)
            ->execute(
                $this->createMock(ChefInterface::class),
                $values,
                new stdClass()
            );
    }

    public function testExecuteWithBasicCounterWithRecipe(): void
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $counter = 2;

        $chef = $this->createMock(ChefInterface::class);
        $subchef = $this->createMock(ChefInterface::class);

        $workplan = ['foo' => 'bar'];

        $chef->expects($this->never())
            ->method('updateWorkPlan');

        $subchef->expects($this->exactly(2))
            ->method('updateWorkPlan')
            ->with($workplan)
            ->willReturnSelf();

        $chef->expects($this->exactly(2))
            ->method('reserveAndBegin')
            ->with($recipe)
            ->willReturn($subchef);

        $subchef->expects($this->exactly(2))
            ->method('process')
            ->willReturnSelf();

        $bowl = $this->buildBowl($recipe, $counter);

        self::assertInstanceOf(
            AbstractRecipeBowl::class,
            $bowl->execute(
                $chef,
                $workplan,
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    public function testExecuteWithBasicCallableCounterWithRecipe(): void
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $counter = $this->createMock(BowlInterface::class);
        $counter->expects($this->exactly(3))
            ->method('execute')
            ->willReturnCallback(function (ChefInterface $chef, array $workplan) use ($counter): MockObject {
                if ($workplan['counter'] >= 3) {
                    $workplan['bowl']->stopLooping();
                }

                self::assertEquals('bar', $workplan['foo']);
                return $counter;
            });

        $chef = $this->createMock(ChefInterface::class);
        $subchef = $this->createMock(ChefInterface::class);

        $workplan = ['foo' => 'bar'];

        $chef->expects($this->never())
            ->method('updateWorkPlan');

        $subchef->expects($this->exactly(3))
            ->method('updateWorkPlan')
            ->with($workplan)
            ->willReturnSelf();

        $chef->expects($this->exactly(3))
            ->method('reserveAndBegin')
            ->with($recipe)
            ->willReturn($subchef);

        $subchef->expects($this->exactly(3))
            ->method('process')
            ->willReturnSelf();

        $bowl = $this->buildBowl($recipe, $counter);

        self::assertInstanceOf(
            AbstractRecipeBowl::class,
            $bowl->execute(
                $chef,
                $workplan,
                $this->createMock(CookingSupervisorInterface::class),
            )
        );

        self::assertEquals(
            ['foo' => 'bar'],
            $workplan
        );
    }

    public function testExecuteWithBasicCounterWithBaseRecipe(): void
    {
        $recipe = $this->createMock(BaseRecipeInterface::class);
        $counter = 2;

        $chef = $this->createMock(ChefInterface::class);
        $subchef = $this->createMock(ChefInterface::class);

        $workplan = ['foo' => 'bar'];

        $chef->expects($this->never())
            ->method('updateWorkPlan');

        $subchef->expects($this->exactly(2))
            ->method('updateWorkPlan')
            ->with($workplan)
            ->willReturnSelf();

        $chef->expects($this->exactly(2))
            ->method('reserveAndBegin')
            ->with($recipe)
            ->willReturn($subchef);

        $subchef->expects($this->exactly(2))
            ->method('process')
            ->willReturnSelf();

        $bowl = $this->buildBowl($recipe, $counter);

        self::assertInstanceOf(
            AbstractRecipeBowl::class,
            $bowl->execute(
                $chef,
                $workplan,
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    public function testExecuteWithBasicCallableCounterWithBaseRecipe(): void
    {
        $recipe = $this->createMock(BaseRecipeInterface::class);
        $counter = $this->createMock(BowlInterface::class);
        $counter->expects($this->exactly(3))
            ->method('execute')
            ->willReturnCallback(function (ChefInterface $chef, array $workplan) use ($counter): MockObject {
                if ($workplan['counter'] >= 3) {
                    $workplan['bowl']->stopLooping();
                }

                self::assertEquals('bar', $workplan['foo']);
                return $counter;
            });

        $chef = $this->createMock(ChefInterface::class);
        $subchef = $this->createMock(ChefInterface::class);

        $workplan = ['foo' => 'bar'];

        $chef->expects($this->never())
            ->method('updateWorkPlan');

        $subchef->expects($this->exactly(3))
            ->method('updateWorkPlan')
            ->with($workplan)
            ->willReturnSelf();

        $chef->expects($this->exactly(3))
            ->method('reserveAndBegin')
            ->with($recipe)
            ->willReturn($subchef);

        $subchef->expects($this->exactly(3))
            ->method('process')
            ->willReturnSelf();

        $bowl = $this->buildBowl($recipe, $counter);

        self::assertInstanceOf(
            AbstractRecipeBowl::class,
            $bowl->execute(
                $chef,
                $workplan,
                $this->createMock(CookingSupervisorInterface::class),
            )
        );

        self::assertEquals(
            ['foo' => 'bar'],
            $workplan
        );
    }
}
