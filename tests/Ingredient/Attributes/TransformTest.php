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

namespace Teknoo\Tests\Recipe\Ingredient\Attributes;

use RuntimeException;
use TypeError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Tests\Recipe\Transformable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Transform::class)]
final class TransformTest extends TestCase
{
    public function testEmptyClass(): void
    {
        $this->assertInstanceOf(
            Transform::class,
            new Transform()
        );
    }
    public function testValidClass(): void
    {
        $this->assertInstanceOf(
            Transform::class,
            $transform = new Transform(Transformable::class)
        );

        $this->assertEquals(
            Transformable::class,
            $transform->getClassName()
        );
    }
    public function testInvalidClass(): void
    {
        $this->expectException(RuntimeException::class);
        new Transform('fooBar');
    }
    public function testValidTransformer(): void
    {
        $callable = function (): void {};
        $this->assertInstanceOf(
            Transform::class,
            $transform = new Transform(
                null,
                $callable
            )
        );

        $this->assertEquals(
            $callable,
            $transform->getTransformer()
        );
    }
    public function testInvalidTransformer(): void
    {
        $this->expectException(TypeError::class);
        new Transform(null, 'fooBar');
    }
}
