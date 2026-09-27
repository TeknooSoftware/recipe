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

namespace Teknoo\Tests\Recipe\Plan;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Recipe;
use Teknoo\Tests\Recipe\Support\EditablePlanExample;

/**
 * Regression test about an additional step added at the position 0 in an editable plan.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversTrait(EditablePlanTrait::class)]
final class EditablePlanPositionZeroTest extends TestCase
{
    public function testAdditionalStepAtPositionZeroRunsFirst(): void
    {
        $log = [];

        $plan = new EditablePlanExample();
        $plan->fill(
            (new Recipe())->cook(
                function () use (&$log): void {
                    $log[] = 'base';
                },
                'base',
                [],
                1,
            )
        );

        $plan->add(
            function () use (&$log): void {
                $log[] = 'added';
            },
            0,
        );

        $chef = new Chef();
        $chef->read($plan);
        $chef->process([]);

        $this->assertEquals(['added', 'base'], $log);
    }
}
