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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Bowl;

use Fiber;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;

/**
 * Bowl to execute a callable available into the workplan in a fiber, not available directly at the recipe writing
 *
 * @see BowlInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class DynamicFiberBowl extends AbstractDynamicBowl
{
    protected function processToExecution(
        callable $callable,
        ChefInterface $chef,
        array &$workPlan,
        ?CookingSupervisorInterface $cookingSupervisor,
    ): void {
        $fiber = new Fiber($callable);
        $values = $this->extractParameters($callable, $chef, $workPlan, $fiber, $cookingSupervisor);

        if (null !== $cookingSupervisor) {
            $cookingSupervisor->supervise($fiber);
        }

        $fiber->start(...$values);
    }
}
