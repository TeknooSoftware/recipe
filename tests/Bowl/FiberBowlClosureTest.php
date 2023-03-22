<?php

/*
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

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Bowl;

use DateTime;
use Fiber;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\FiberBowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Bowl\FiberBowl
 * @covers \Teknoo\Recipe\Bowl\BowlTrait
 */
class FiberBowlClosureTest extends AbstractBowlTests
{
    protected function getCallable()
    {
        $that = $this;

        return function (
            ChefInterface $chef,
            string $bar,
            $bar2,
            $foo2,
            DateTime $date,
            $_methodName,
            Fiber $fiber,
            CookingSupervisorInterface $cookingSupervisor,
        ) use ($that) {
            $that->assertInstanceOf(
                Fiber::class,
                $fiber
            );

            $that->assertInstanceOf(
                CookingSupervisorInterface::class,
                $cookingSupervisor
            );

            $chef->continue([
                'bar' => $bar,
                'bar2' => $bar,
                'foo2' => $foo2,
                'date' => $date->getTimestamp(),
                '_methodName' => $_methodName,
            ]);
        };
    }

    protected function getMapping()
    {
        return ['bar' => 'foo', 'bar2' => ['bar', 'foo']];
    }

    /**
     * @inheritDoc
     */
    public function buildBowl(): BowlInterface
    {
        return new FiberBowl(
            $this->getCallable(),
            $this->getMapping(),
            'bowlClass'
        );
    }

    public function testExecuteWithOptional()
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects(self::once())
            ->method('continue')
            ->with([
                'date' => (new DateTime('2018-01-01'))->getTimestamp(),
                'opt1' => 123,
                'opt2' => null,
                'opt3' => 'foo',
            ])
            ->willReturnSelf();

        $chef->expects(self::never())
            ->method('updateWorkPlan');

        $closure = function (ChefInterface $chef, DateTime $date, $opt1 = 123, $opt2 = null, $opt3 = null) {
            $chef->continue([
                'date' => $date->getTimestamp(),
                'opt1' => $opt1,
                'opt2' => $opt2,
                'opt3' => $opt3,
            ]);
        };

        $bowl = new FiberBowl(
            $closure,
            $this->getMapping(),
            'bowlClass'
        );

        $values = [
            'now' => (new DateTime('2018-01-01')),
            'opt3' => 'foo',
        ];

        self::assertInstanceOf(
            BowlInterface::class,
            $bowl->execute(
                $chef,
                $values,
                $this->createMock(CookingSupervisorInterface::class)
            )
        );
    }
}
