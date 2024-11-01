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

namespace Teknoo\Tests\Recipe\Bowl;

use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\Recipe\Bowl\AbstractRecipeBowl;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(RecipeBowl::class)]
#[CoversClass(AbstractRecipeBowl::class)]
final class RecipeBowlTest extends AbstractRecipeBowlTests
{
    /**
     * @param RecipeInterface $recipe
     * @param int $repeat
     * @return RecipeBowl
     */
    public function buildBowl($recipe, $repeat): AbstractRecipeBowl
    {
        return new RecipeBowl($recipe, $repeat);
    }
}
