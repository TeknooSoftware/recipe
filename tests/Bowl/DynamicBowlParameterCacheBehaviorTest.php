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
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Bowl;

use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\AbstractDynamicBowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\BowlTrait;
use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Value;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AbstractDynamicBowl::class)]
#[CoversClass(DynamicBowl::class)]
final class DynamicBowlParameterCacheBehaviorTest extends TestCase
{
    protected function getCallableObject(): callable
    {
        $object = new class () {
            public function methodToCall(ChefInterface $chef, string $bar, $foo2, DateTime $date): void
            {
                $chef->continue([
                    'bar' => $bar,
                    'foo2' => $foo2,
                    'date' => $date->getTimestamp()
                ]);
            }
        };

        return $object->methodToCall(...);
    }
    protected function getCallableInvokable(): callable
    {
        $object = new class () {
            public function __invoke(ChefInterface $chef, $bar, $foo, DateTime $date): void
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
    protected function getMapping(): array
    {
        return ['bar' => 'foo'];
    }
    public function buildBowl(): BowlInterface
    {
        return new DynamicBowl(
            'callableToExec',
            false,
            $this->getMapping()
        );
    }
    public function buildBowlWithMappingValue(): BowlInterface
    {
        return new DynamicBowl(
            'callableToExec',
            false,
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
        $chef->expects($this->exactly(3))
            ->method('continue')
            ->with(
                $this->callback(
                    fn ($value): bool => match ($value) {
                        ['bar' => 'foo', 'foo2' => 'bar2', 'date' => (new DateTime('2018-01-01'))->getTimestamp()] => true,
                        ['bar' => 'foo', 'foo2' => 'bar2', 'date' => (new DateTime('2018-01-01'))->getTimestamp()] => true,
                        ['bar' => 'foo', 'foo2' => 'foo', 'date' => (new DateTime('2018-01-01'))->getTimestamp()] => true,
                        default => false,
                    }
                ),
            )
            ->willReturnSelf();

        $chef->expects($this->never())
            ->method('updateWorkPlan');

        $values = [
            'foo' => 'foo',
            'foo2' => 'bar2',
            'now' => (new DateTime('2018-01-01')),
            'callableToExec' => $this->getCallableObject()
        ];

        $bowl = $this->buildBowl();
        self::assertInstanceOf(BowlInterface::class, $bowl->execute($chef, $values));
        self::assertInstanceOf(BowlInterface::class, $bowl->execute($chef, $values));

        $values['callableToExec'] = $this->getCallableInvokable();
        self::assertInstanceOf(BowlInterface::class, $bowl->execute($chef, $values));
    }
}
