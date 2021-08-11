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

namespace Teknoo\Tests\Recipe\Ingredient\Attributes;

use PHPUnit\Framework\TestCase;
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
 * @covers \Teknoo\Recipe\Ingredient\Attributes\Transform
 */
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
}