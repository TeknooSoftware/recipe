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
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Recipe;

/**
 * Regression test about static methods passed to a bowl as a "Class::method" string.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
final class BowlStaticMethodStringTest extends TestCase
{
    /**
     * @var string[]
     */
    public static array $log = [];

    public static function record(string $value, int $times = 1): void
    {
        self::$log[] = str_repeat($value, $times);
    }

    public function testExecuteWithClassMethodStringCallable(): void
    {
        self::$log = [];
        $bowl = new Bowl(self::class . '::record', ['value' => 'text'], 'record');

        $workPlan = ['text' => 'ab', 'times' => 2];
        $this->assertInstanceOf(
            BowlInterface::class,
            $bowl->execute($this->createStub(ChefInterface::class), $workPlan),
        );

        $this->assertEquals(['abab'], self::$log);
    }

    public function testCookWithClassMethodStringCallable(): void
    {
        self::$log = [];
        $recipe = (new Recipe())->cook(self::class . '::record', 'record', ['value' => 'text']);

        (new Chef($recipe))->process(['text' => 'cd']);

        $this->assertEquals(['cd'], self::$log);
    }
}
