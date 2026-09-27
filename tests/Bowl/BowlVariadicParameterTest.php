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
use Teknoo\Recipe\ChefInterface;

/**
 * Regression tests about variadic parameters of a bowl's callable.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
final class BowlVariadicParameterTest extends TestCase
{
    /**
     * @var array<int, array{0: string, 1: string[]}>
     */
    private array $log = [];

    private function buildBowl(): BowlInterface
    {
        return new Bowl(
            function (string $a, string ...$rest): void {
                $this->log[] = [$a, $rest];
            },
            [],
            'variadic',
        );
    }

    public function testOptionalVariadicParameterIsSkipped(): void
    {
        $workPlan = ['a' => 'A'];
        $this->assertInstanceOf(
            BowlInterface::class,
            $this->buildBowl()->execute($this->createStub(ChefInterface::class), $workPlan),
        );

        $this->assertEquals([['A', []]], $this->log);
    }

    public function testVariadicParameterReceivesWorkplanValueByName(): void
    {
        $workPlan = ['a' => 'A', 'rest' => 'R'];
        $this->assertInstanceOf(
            BowlInterface::class,
            $this->buildBowl()->execute($this->createStub(ChefInterface::class), $workPlan),
        );

        $this->assertEquals([['A', ['R']]], $this->log);
    }
}
