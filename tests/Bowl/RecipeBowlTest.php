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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Bowl;

use Teknoo\Recipe\Bowl\AbstractRecipeBowl;
use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Bowl\AbstractRecipeBowl
 * @covers \Teknoo\Recipe\Bowl\RecipeBowl
 */
class RecipeBowlTest extends AbstractRecipeBowlTest
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
