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

namespace Teknoo\Tests\Recipe\Ingredient\Attributes;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Tests\Recipe\Transformable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Transform::class)]
class TransformTest extends TestCase
{
    public function testEmptyClass()
    {
        self::assertInstanceOf(
            Transform::class,
            new Transform()
        );
    }

    public function testValidClass()
    {
        self::assertInstanceOf(
            Transform::class,
            $transform = new Transform(Transformable::class)
        );

        self::assertEquals(
            Transformable::class,
            $transform->getClassName()
        );
    }

    public function testInvalidClass()
    {
        $this->expectException(\RuntimeException::class);
        new Transform('fooBar');
    }

    public function testValidTransformer()
    {
        $callable = function () {};
        self::assertInstanceOf(
            Transform::class,
            $transform = new Transform(
                null,
                $callable
            )
        );

        self::assertEquals(
            $callable,
            $transform->getTransformer()
        );
    }

    public function testInvalidTransformer()
    {
        $this->expectException(\TypeError::class);
        new Transform(null, 'fooBar');
    }
}
