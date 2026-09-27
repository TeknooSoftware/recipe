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

use BadMethodCallException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;

/**
 * Regression tests about a list of aliases in a bowl's mapping containing a falsy name.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
final class BowlMappingWithFalsyNameTest extends TestCase
{
    /**
     * @var string[]
     */
    private array $log = [];

    /**
     * @param string[] $aliases
     */
    private function buildBowl(array $aliases): BowlInterface
    {
        return new Bowl(
            function (string $param): void {
                $this->log[] = $param;
            },
            ['param' => $aliases],
            'mapped',
        );
    }

    public function testMappingListFallsThroughToNameZero(): void
    {
        $workPlan = ['0' => 'zero'];
        $this->assertInstanceOf(
            BowlInterface::class,
            $this->buildBowl(['missing', '0', 'other'])->execute($this->createStub(ChefInterface::class), $workPlan),
        );

        $this->assertEquals(['zero'], $this->log);
    }

    public function testMappingListContinuesAfterAFalsyName(): void
    {
        $workPlan = ['other' => 'found'];
        $this->assertInstanceOf(
            BowlInterface::class,
            $this->buildBowl(['missing', '0', 'other'])->execute($this->createStub(ChefInterface::class), $workPlan),
        );

        $this->assertEquals(['found'], $this->log);
    }

    public function testMappingListFallsBackToLastNameWhenNoneFound(): void
    {
        $workPlan = [];

        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Missing the parameter param (last)');
        $this->buildBowl(['first', 'last'])->execute($this->createStub(ChefInterface::class), $workPlan);
    }
}
