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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\BowlTrait;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Tests\Recipe\Transformable;

use function explode;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
final class BowlWithTransformableTest extends TestCase
{
    private bool $called = false;
    public function noTransform(string $param1, Transformable $param2): void
    {
        $this->assertEquals(
            'foo',
            $param1
        );

        $this->assertInstanceOf(Transformable::class, $param2);

        $this->assertEquals(
            new Transformable(['foo' => 'bar']),
            $param2
        );

        $this->called = true;
    }
    public function transformNoHinting(string $param1, #[Transform] mixed $param2): void
    {
        $this->assertEquals(
            'foo',
            $param1
        );

        $this->assertIsArray($param2);

        $this->assertEquals(
            ['foo' => 'bar'],
            $param2
        );

        $this->called = true;
    }
    public function transformHinting(string $param1, #[Transform] array $param2): void
    {
        $this->assertEquals(
            'foo',
            $param1
        );

        $this->assertIsArray($param2);

        $this->assertEquals(
            ['foo' => 'bar'],
            $param2
        );

        $this->called = true;
    }
    public function transformHintingWithClass(string $param1, #[Transform(Transformable::class)] array $anotherName): void
    {
        $this->assertEquals(
            'foo',
            $param1
        );

        $this->assertIsArray($anotherName);

        $this->assertEquals(
            ['foo' => 'bar'],
            $anotherName
        );

        $this->called = true;
    }
    public static function transformerToArray(string $value): array
    {
        return explode('-', $value);
    }
    public static function transformerToArray2(string $value): Transformable
    {
        return new Transformable(explode('-', $value));
    }
    public function transformWithTransformer(
        string $param1,
        #[Transform(null, [self::class, 'transformerToArray2'])] array $param2
    ): void {
        $this->assertEquals(
            'foo',
            $param1
        );

        $this->assertIsArray($param2);

        $this->assertEquals(
            ['foo', 'bar'],
            $param2
        );

        $this->called = true;
    }
    public function transformHintingWithClassAndTransformer(
        string $param1,
        #[Transform(Transformable::class, [self::class, 'transformerToArray'])] array $anotherName
    ): void {
        $this->assertEquals(
            'foo',
            $param1
        );

        $this->assertIsArray($anotherName);

        $this->assertEquals(
            ['foo', 'bar'],
            $anotherName
        );

        $this->called = true;
    }
    public function testWithoutAttributeTransform(): void
    {
        $workplan = [
            'param1' => 'foo',
            'param2' => new Transformable(['foo' => 'bar'])
        ];

        $this->called = false;

        $this->assertInstanceOf(
            BowlInterface::class,
            (new Bowl($this->noTransform(...), []))->execute(
                $this->createMock(ChefInterface::class),
                $workplan
            )
        );

        $this->assertTrue($this->called);
    }
    public function testWithAttributeTransformWithoutTypeHinting(): void
    {
        $workplan = [
            'param1' => 'foo',
            'param2' => new Transformable(['foo' => 'bar'])
        ];

        $this->called = false;

        $this->assertInstanceOf(
            BowlInterface::class,
            (new Bowl($this->transformNoHinting(...), []))->execute(
                $this->createMock(ChefInterface::class),
                $workplan
            )
        );

        $this->assertTrue($this->called);
    }
    public function testWithAttributeTransformWithTypeHinting(): void
    {
        $workplan = [
            'param1' => 'foo',
            'param2' => new Transformable(['foo' => 'bar'])
        ];

        $this->called = false;

        $this->assertInstanceOf(
            BowlInterface::class,
            (new Bowl($this->transformHinting(...), []))->execute(
                $this->createMock(ChefInterface::class),
                $workplan
            )
        );

        $this->assertTrue($this->called);
    }
    public function testWithAttributeTransformWithTypeHintingWithClass(): void
    {
        $workplan = [
            'param1' => 'foo',
            'param2' => new Transformable(['foo' => 'bar'])
        ];

        $this->called = false;

        $this->assertInstanceOf(
            BowlInterface::class,
            (new Bowl($this->transformHintingWithClass(...), []))->execute(
                $this->createMock(ChefInterface::class),
                $workplan
            )
        );

        $this->assertTrue($this->called);
    }
    public function testWithAttributeTransformWithTransformer(): void
    {
        $workplan = [
            'param1' => 'foo',
            'param2' => 'foo-bar'
        ];

        $this->called = false;

        $this->assertInstanceOf(
            BowlInterface::class,
            (new Bowl($this->transformWithTransformer(...), []))->execute(
                $this->createMock(ChefInterface::class),
                $workplan
            )
        );

        $this->assertTrue($this->called);
    }
    public function testWithAttributeTransformWithTypeHintingWithClassAndWithTransformer(): void
    {
        $workplan = [
            'param1' => 'foo',
            'param2' => 'foo-bar',
        ];

        $this->called = false;

        $this->assertInstanceOf(
            BowlInterface::class,
            (new Bowl($this->transformWithTransformer(...), []))->execute(
                $this->createMock(ChefInterface::class),
                $workplan
            )
        );

        $this->assertTrue($this->called);
    }
}
