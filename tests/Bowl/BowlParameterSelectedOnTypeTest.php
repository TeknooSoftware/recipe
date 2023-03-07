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

use Teknoo\Recipe\Bowl\Bowl;
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
 * @covers \Teknoo\Recipe\Bowl\Bowl
 * @covers \Teknoo\Recipe\Bowl\BowlTrait
 */
class BowlParameterSelectedOnTypeTest extends AbstractBowlTests
{
    protected function getCallable()
    {
        $object = new class() {
            public function methodToCall(
                ChefInterface $chef,
                string $bar,
                $bar2,
                $foo2,
                \DateTimeInterface $date,
                $_methodName
            ) {
                $chef->continue([
                    'bar' => $bar,
                    'bar2' => $bar2,
                    'foo2' => $foo2,
                    'date' => $date->getTimestamp(),
                    '_methodName' => $_methodName,
                ]);
            }
        };

        return [$object, 'methodToCall'];
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
        return new Bowl(
            $this->getCallable(),
            $this->getMapping(),
            'bowlClass'
        );
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
                'date' => (new \DateTime('2018-01-02'))->getTimestamp(),
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
                $values
            )
        );
    }
}
