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

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Bowl\AbstractDynamicBowl
 * @covers \Teknoo\Recipe\Bowl\DynamicBowl
 * @covers \Teknoo\Recipe\Bowl\BowlTrait
 */
class DynamicBowlParameterCacheBehaviorTest extends TestCase
{
    protected function getCallableObject()
    {
        $object = new class() {
            public function methodToCall(ChefInterface $chef, string $bar, $foo2, \DateTime $date)
            {
                $chef->continue([
                    'bar' => $bar,
                    'foo2' => $foo2,
                    'date' => $date->getTimestamp()
                ]);
            }
        };

        return [$object, 'methodToCall'];
    }

    protected function getCallableInvokable(): callable
    {
        $object = new class() {
            public function __invoke(ChefInterface $chef, $bar, $foo, \DateTime $date)
            {
                $chef->continue([
                    'bar' => $bar,
                    'foo2' => $foo,
                    'date' => $date->getTimestamp()
                ]);
            }
        };

        return $object;
    }

    protected function getMapping()
    {
        return ['bar' => 'foo'];
    }

    /**
     * @inheritDoc
     */
    public function buildBowl(): BowlInterface
    {
        return new DynamicBowl(
            'callableToExec',
            false,
            $this->getMapping()
        );
    }


    public function testExecute()
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects(self::exactly(3))
            ->method('continue')
            ->with(
                $this->callback(
                    fn ($value) => match ($value) {
                        ['bar' => 'foo', 'foo2' => 'bar2', 'date' => (new \DateTime('2018-01-01'))->getTimestamp()] => true,
                        ['bar' => 'foo', 'foo2' => 'bar2', 'date' => (new \DateTime('2018-01-01'))->getTimestamp()] => true,
                        ['bar' => 'foo', 'foo2' => 'foo', 'date' => (new \DateTime('2018-01-01'))->getTimestamp()] => true,
                        default => false,
                    }
                ),
            )
            ->willReturnSelf();

        $chef->expects(self::never())
            ->method('updateWorkPlan');

        $values = [
            'foo' => 'foo',
            'foo2' => 'bar2',
            'now' => (new \DateTime('2018-01-01')),
            'callableToExec' => $this->getCallableObject()
        ];

        $bowl = $this->buildBowl();
        self::assertInstanceOf(BowlInterface::class, $bowl->execute($chef, $values));
        self::assertInstanceOf(BowlInterface::class, $bowl->execute($chef, $values));

        $values['callableToExec'] = $this->getCallableInvokable();
        self::assertInstanceOf(BowlInterface::class, $bowl->execute($chef, $values));
    }
}
