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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Bowl;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\AbstractRecipeBowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
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

    public function testExceptionOnBadRecipe()
    {
        $this->expectException(\TypeError::class);
        $this->buildBowl(new \stdClass());
    }

    public function testExceptionOnExecuteWithBadChef()
    {
        $this->expectException(\TypeError::class);
        $values = ['foo'=>'bar'];
        $this->buildBowl($this->createMock(RecipeInterface::class), 1)
            ->execute(
                new \stdClass(),
                $values,
                $this->createMock(CookingSupervisorInterface::class),
            );
    }

    public function testExceptionOnExecuteWithBadWorkPlan()
    {
        $this->expectException(\TypeError::class);
        $values = new \stdClass();
        $this->buildBowl($this->createMock(RecipeInterface::class), 1)
            ->execute(
                $this->createMock(ChefInterface::class),
                $values,
                $this->createMock(CookingSupervisorInterface::class),
            );
    }

    public function testExceptionOnExecuteWithBadSupervisor()
    {
        $this->expectException(\TypeError::class);
        $values = [];
        $this->buildBowl($this->createMock(RecipeInterface::class), 1)
            ->execute(
                $this->createMock(ChefInterface::class),
                $values,
                new \stdClass()
            );
    }

    public function testExecuteWithBasicCounterWithRecipe()
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $counter = 2;

        $chef = $this->createMock(ChefInterface::class);
        $subchef = $this->createMock(ChefInterface::class);

        $workplan = ['foo' => 'bar'];

        $chef->expects(self::never())
            ->method('updateWorkPlan');

        $subchef->expects(self::exactly(2))
            ->method('updateWorkPlan')
            ->with($workplan)
            ->willReturnSelf();

        $chef->expects(self::exactly(2))
            ->method('reserveAndBegin')
            ->with($recipe)
            ->willReturn($subchef);

        $subchef->expects(self::exactly(2))
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

    public function testExecuteWithBasicCallableCounterWithRecipe()
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $counter = $this->createMock(BowlInterface::class);
        $counter->expects(self::exactly(3))
            ->method('execute')
            ->willReturnCallback(function (ChefInterface $chef, $workplan) use ($counter) {
                if ($workplan['counter'] >= 3) {
                    $workplan['bowl']->stopLooping();
                }

                self::assertEquals('bar', $workplan['foo']);
                return $counter;
            });

        $chef = $this->createMock(ChefInterface::class);
        $subchef = $this->createMock(ChefInterface::class);

        $workplan = ['foo' => 'bar'];

        $chef->expects(self::never())
            ->method('updateWorkPlan');

        $subchef->expects(self::exactly(3))
            ->method('updateWorkPlan')
            ->with($workplan)
            ->willReturnSelf();

        $chef->expects(self::exactly(3))
            ->method('reserveAndBegin')
            ->with($recipe)
            ->willReturn($subchef);

        $subchef->expects(self::exactly(3))
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

    public function testExecuteWithBasicCounterWithBaseRecipe()
    {
        $recipe = $this->createMock(BaseRecipeInterface::class);
        $counter = 2;

        $chef = $this->createMock(ChefInterface::class);
        $subchef = $this->createMock(ChefInterface::class);

        $workplan = ['foo' => 'bar'];

        $chef->expects(self::never())
            ->method('updateWorkPlan');

        $subchef->expects(self::exactly(2))
            ->method('updateWorkPlan')
            ->with($workplan)
            ->willReturnSelf();

        $chef->expects(self::exactly(2))
            ->method('reserveAndBegin')
            ->with($recipe)
            ->willReturn($subchef);

        $subchef->expects(self::exactly(2))
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

    public function testExecuteWithBasicCallableCounterWithBaseRecipe()
    {
        $recipe = $this->createMock(BaseRecipeInterface::class);
        $counter = $this->createMock(BowlInterface::class);
        $counter->expects(self::exactly(3))
            ->method('execute')
            ->willReturnCallback(function (ChefInterface $chef, $workplan) use ($counter) {
                if ($workplan['counter'] >= 3) {
                    $workplan['bowl']->stopLooping();
                }

                self::assertEquals('bar', $workplan['foo']);
                return $counter;
            });

        $chef = $this->createMock(ChefInterface::class);
        $subchef = $this->createMock(ChefInterface::class);

        $workplan = ['foo' => 'bar'];

        $chef->expects(self::never())
            ->method('updateWorkPlan');

        $subchef->expects(self::exactly(3))
            ->method('updateWorkPlan')
            ->with($workplan)
            ->willReturnSelf();

        $chef->expects(self::exactly(3))
            ->method('reserveAndBegin')
            ->with($recipe)
            ->willReturn($subchef);

        $subchef->expects(self::exactly(3))
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
