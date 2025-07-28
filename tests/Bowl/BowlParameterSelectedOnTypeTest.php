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

use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\BowlTrait;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\Value;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
final class BowlParameterSelectedOnTypeTest extends AbstractBowlTests
{
    protected function getCallable(): callable
    {
        $object = new class () {
            public function methodToCall(
                ChefInterface $chef,
                string $bar,
                $bar2,
                $foo2,
                DateTimeInterface $date,
                $_methodName
            ): void {
                $chef->continue([
                    'bar' => $bar,
                    'bar2' => $bar2,
                    'foo2' => $foo2,
                    'date' => $date->getTimestamp(),
                    '_methodName' => $_methodName,
                ]);
            }
        };

        return $object->methodToCall(...);
    }
    protected function getMapping(): array
    {
        return ['bar' => 'foo', 'bar2' => ['bar', 'foo']];
    }
    public function buildBowl(): BowlInterface
    {
        return new Bowl(
            $this->getCallable(),
            $this->getMapping(),
            'bowlClass'
        );
    }
    public function buildBowlWithMappingValue(): BowlInterface
    {
        return new Bowl(
            $this->getCallable(),
            [
                'bar' => new Value('ValueFoo1'),
                'bar2' => new Value('ValueFoo2'),
            ],
            'bowlClass'
        );
    }
    public function testExecute(): void
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects($this->once())
            ->method('continue')
            ->with([
                'bar' => 'foo',
                'bar2' => 'foo',
                'foo2' => 'bar2',
                'date' => (new DateTime('2018-01-02'))->getTimestamp(),
                '_methodName' => 'bowlClass',
            ])
            ->willReturnSelf();

        $chef->expects($this->never())
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
    public function testExecuteWithValue(): void
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects($this->once())
            ->method('continue')
            ->with([
                'bar' => 'ValueFoo1',
                'bar2' => 'ValueFoo2',
                'foo2' => 'bar2',
                'date' => (new DateTime('2018-01-02'))->getTimestamp(),
                '_methodName' => 'bowlClass',
            ])
            ->willReturnSelf();

        $chef->expects($this->never())
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
}
