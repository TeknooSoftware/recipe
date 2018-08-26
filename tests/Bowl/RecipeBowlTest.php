<?php

/**
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\Recipe\Bowl;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Bowl\RecipeBowl
 */
class RecipeBowlTest extends TestCase
{
    /**
     * @param RecipeInterface $recipe
     * @param int $repeat
     * @return RecipeBowl
     */
    public function buildBowl($recipe, $repeat): RecipeBowl
    {
        return new RecipeBowl($recipe, $repeat);
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnBadRecipe()
    {
        $this->buildBowl(new \stdClass());
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnExecuteWithBadChef()
    {
        $values = ['foo'=>'bar'];
        $this->buildBowl($this->createMock(RecipeInterface::class), 1)
            ->execute(new \stdClass(), $values);
    }

    /**
     * @expectedException \TypeError
     */
    public function testExceptionOnExecuteWithBadWorkPlan()
    {
        $values = new \stdClass();
        $this->buildBowl($this->createMock(RecipeInterface::class), 1)
            ->execute($this->createMock(ChefInterface::class), $values);
    }

    public function testExecuteWithBasicCounter()
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
            ->with([])
            ->willReturnSelf();

        $bowl = $this->buildBowl($recipe, $counter);

        self::assertInstanceOf(
            RecipeBowl::class,
            $bowl->execute($chef, $workplan)
        );
    }

    public function testExecuteWithBasicCallableCounter()
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
            ->with([])
            ->willReturnSelf();

        $bowl = $this->buildBowl($recipe, $counter);

        self::assertInstanceOf(
            RecipeBowl::class,
            $bowl->execute($chef, $workplan)
        );

        self::assertEquals(
            ['foo' => 'bar'],
            $workplan
        );
    }
}
