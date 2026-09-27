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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhp;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Tests\Recipe\Php85\FirstClassCallableTransformers;

/**
 * Tests about a first-class callable used as transformer in the Transform attribute (allowed in attributes since
 * PHP 8.5, skipped on older versions). The steps live in a dedicated fixture, never loaded on PHP 8.4.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Transform::class)]
#[CoversClass(Bowl::class)]
#[RequiresPhp('>= 8.5.0')]
final class TransformWithFirstClassCallableTest extends TestCase
{
    public function testTransformerAsFirstClassCallableOfAStaticMethod(): void
    {
        //The fixture is loaded here only, after the PHP version requirement check
        FirstClassCallableTransformers::$log = [];
        $bowl = new Bowl([FirstClassCallableTransformers::class, 'withStaticMethodTransformer'], [], 'transform');

        $workPlan = ['date' => '2020-01-01'];
        $this->assertInstanceOf(
            BowlInterface::class,
            $bowl->execute($this->createStub(ChefInterface::class), $workPlan),
        );

        $this->assertEquals(['2020-01-01'], FirstClassCallableTransformers::$log);
    }

    public function testTransformerAsFirstClassCallableOfAnInternalFunction(): void
    {
        FirstClassCallableTransformers::$log = [];
        $bowl = new Bowl([FirstClassCallableTransformers::class, 'withInternalFunctionTransformer'], [], 'upper');

        $workPlan = ['value' => 'foo'];
        $this->assertInstanceOf(
            BowlInterface::class,
            $bowl->execute($this->createStub(ChefInterface::class), $workPlan),
        );

        $this->assertEquals(['FOO'], FirstClassCallableTransformers::$log);
    }
}
