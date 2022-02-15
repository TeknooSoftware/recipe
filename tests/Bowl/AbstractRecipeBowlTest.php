<?php

/**
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\Recipe\Bowl;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\AbstractRecipeBowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractRecipeBowlTest extends TestCase
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
            ->execute(new \stdClass(), $values);
    }

    public function testExceptionOnExecuteWithBadWorkPlan()
    {
        $this->expectException(\TypeError::class);
        $values = new \stdClass();
        $this->buildBowl($this->createMock(RecipeInterface::class), 1)
            ->execute($this->createMock(ChefInterface::class), $values);
    }

    public function testExecuteWithBasicCounterWithRecipe()
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $counter = 2;

        $chef = $this->createMock(ChefInterface::class);
        $subchef = $this->createMock(ChefInterface::class);

        $workplan = ['foo' => 'bar'];

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
            $bowl->execute($chef, $workplan)
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
            $bowl->execute($chef, $workplan)
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
            $bowl->execute($chef, $workplan)
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
            $bowl->execute($chef, $workplan)
        );

        self::assertEquals(
            ['foo' => 'bar'],
            $workplan
        );
    }
}
