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
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Bowl;

use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;

/**
 * Bowl to execute a new recipe, with a new trained chef provided by the current chef, but sharing the a clone of the
 * original workplan.
 *
 * @see BowlInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class RecipeBowl extends AbstractRecipeBowl
{
    protected function processToExecution(
        ChefInterface $subchef,
        ?CookingSupervisorInterface $cookingSupervisor,
    ): void {
        $subchef->process(
            [
                CookingSupervisorInterface::class => $cookingSupervisor,
            ]
        );
    }
}
