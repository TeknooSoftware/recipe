<?php

declare(strict_types=1);

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
namespace Teknoo\Tests\Recipe\Bowl;

use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionParameter;
use RuntimeException;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\BowlTrait;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Value;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
final class BowlClassTest extends AbstractBowlTests
{
    /**
     * @param DateTime|DateTimeImmutable $date
     */
    public static function methodToCall(
        ChefInterface $chef,
        string $bar,
        $bar2,
        $foo2,
        DateTime|DateTimeImmutable $date,
        $_methodName,
        self $self,
    ): void {
        $chef->continue([
            'bar' => $bar,
            'bar2' => $bar2,
            'foo2' => $foo2,
            'date' => $date->getTimestamp(),
            '_methodName' => $_methodName,
        ]);
    }

    protected function getCallable(): callable
    {
        return [static::class, 'methodToCall'];
    }
    protected function getMapping(): array
    {
        return ['bar' => 'foo', 'bar2' => ['bar', 'foo']];
    }

    protected function getValidWorkPlan(): array
    {
        return \array_merge(
            parent::getValidWorkPlan(),
            [self::class => $this]
        );
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

    public function testBadDeclaringClassForSelfParameter(): void
    {
        $method = static function (self $parameter): void {};
        $bowl = new Bowl(
            $method,
            $this->getMapping(),
            'bowlClass'
        );

        $refClass = new ReflectionClass(Bowl::class);
        $parameter = $refClass->getProperty('reflectionsParameters');
        $parameter->setAccessible(true);

        $refType = $this->createMock(ReflectionNamedType::class);
        $refType->expects($this->any())
            ->method('getName')
            ->willReturn('self');

        $refParam = $this->createMock(ReflectionParameter::class);
        $refParam->expects($this->any())
            ->method('getType')
            ->willReturn($refType);

        $parameter->setValue(
            $bowl,
            [
                __FILE__ . ':110' => [$refParam]
            ]
        );

        $workplan = ['foo' => 'bar'];

        $this->expectException(RuntimeException::class);
        $bowl->execute(
            $this->createMock(ChefInterface::class),
            $workplan
        );
    }
}
