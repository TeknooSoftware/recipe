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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\Recipe\Bowl;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Tests\Recipe\Transformable;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Bowl\Bowl
 * @covers \Teknoo\Recipe\Bowl\BowlTrait
 */
class BowlWithTransformableTest extends TestCase
{
    private bool $called = false;

    public function noTransform(string $param1, Transformable $param2)
    {
        self::assertEquals(
            'foo',
            $param1
        );

        self::assertInstanceOf(Transformable::class, $param2);

        self::assertEquals(
            new Transformable(['foo' => 'bar']),
            $param2
        );

        $this->called = true;
    }

    public function transformNoHinting(string $param1, #[Transform] mixed $param2)
    {
        self::assertEquals(
            'foo',
            $param1
        );

        self::assertIsArray($param2);

        self::assertEquals(
            ['foo' => 'bar'],
            $param2
        );

        $this->called = true;
    }

    public function transformHinting(string $param1, #[Transform] array $param2)
    {
        self::assertEquals(
            'foo',
            $param1
        );

        self::assertIsArray($param2);

        self::assertEquals(
            ['foo' => 'bar'],
            $param2
        );

        $this->called = true;
    }

    public function testWithoutAttributeTransform()
    {
        $workplan = [
            'param1' => 'foo',
            'param2' => new Transformable(['foo' => 'bar'])
        ];

        $this->called = false;

        self::assertInstanceOf(
            BowlInterface::class,
            (new Bowl([$this, 'noTransform'], []))->execute(
                $this->createMock(ChefInterface::class),
                $workplan
            )
        );

        self::assertTrue($this->called);
    }

    public function testWithAttributeTransformWithoutTypeHinting()
    {
        $workplan = [
            'param1' => 'foo',
            'param2' => new Transformable(['foo' => 'bar'])
        ];

        $this->called = false;

        self::assertInstanceOf(
            BowlInterface::class,
            (new Bowl([$this, 'transformNoHinting'], []))->execute(
                $this->createMock(ChefInterface::class),
                $workplan
            )
        );

        self::assertTrue($this->called);
    }

    public function testWithAttributeTransformWithTypeHinting()
    {
        $workplan = [
            'param1' => 'foo',
            'param2' => new Transformable(['foo' => 'bar'])
        ];

        $this->called = false;

        self::assertInstanceOf(
            BowlInterface::class,
            (new Bowl([$this, 'transformHinting'], []))->execute(
                $this->createMock(ChefInterface::class),
                $workplan
            )
        );

        self::assertTrue($this->called);
    }
}
