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
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Bowl;

use BadMethodCallException;
use DateTime;
use DateTimeInterface;
use stdClass;
use Teknoo\Recipe\Bowl\BowlInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use TypeError;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractBowlTests extends TestCase
{
    /**
     * @return BowlInterface
     */
    abstract public function buildBowl(): BowlInterface;

    /**
     * @return BowlInterface
     */
    abstract public function buildBowlWithMappingValue(): BowlInterface;

    public function testExceptionOnExecuteWithBadChef()
    {
        $this->expectException(TypeError::class);
        $values = ['foo' => 'bar'];
        $this->buildBowl()->execute(new stdClass(), $values);
    }

    public function testExceptionOnExecuteWithBadWorkPlan()
    {
        $this->expectException(TypeError::class);
        $values = new stdClass();
        $this->buildBowl()->execute($this->createMock(ChefInterface::class), $values);
    }

    protected function getValidWorkPlan(): array
    {
        return [
            'foo' => 'foo',
            'foo2' => 'bar2',
            'now' => (new DateTime('2018-01-01')),
            DateTimeInterface::class => (new DateTime('2018-01-02')),
        ];
    }

    public function testExecute()
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects(self::once())
            ->method('continue')
            ->with([
                'bar' => 'foo',
                'bar2' => 'foo',
                'foo2' => 'bar2',
                'date' => (new DateTime('2018-01-01'))->getTimestamp(),
                '_methodName' => 'bowlClass',
            ])
            ->willReturnSelf();

        $chef->expects(self::never())
            ->method('updateWorkPlan');

        $values = $this->getValidWorkPlan();
        self::assertInstanceOf(
            BowlInterface::class,
            $this->buildBowl()->execute(
                $chef,
                $values,
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    public function testExecuteWithValue()
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects(self::once())
            ->method('continue')
            ->with([
                'bar' => 'ValueFoo1',
                'bar2' => 'ValueFoo2',
                'foo2' => 'bar2',
                'date' => (new DateTime('2018-01-01'))->getTimestamp(),
                '_methodName' => 'bowlClass',
            ])
            ->willReturnSelf();

        $chef->expects(self::never())
            ->method('updateWorkPlan');

        $values = $this->getValidWorkPlan();
        self::assertInstanceOf(
            BowlInterface::class,
            $this->buildBowlWithMappingValue()->execute(
                $chef,
                $values,
                $this->createMock(CookingSupervisorInterface::class),
            )
        );
    }

    protected function getNotValidWorkPlan(): array
    {
        return [
            'foo' => 'foo'
        ];
    }

    public function testExceptionWhenExecuteAndMissingAndIngredientInWorkPlan()
    {
        $this->expectException(BadMethodCallException::class);
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects(self::never())
            ->method('continue');

        $chef->expects(self::never())
            ->method('updateWorkPlan');

        $values = $this->getNotValidWorkPlan();
        self::assertInstanceOf(
            BowlInterface::class,
            $this->buildBowl()->execute(
                $chef,
                $values
            )
        );
    }
}
