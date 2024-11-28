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

namespace Teknoo\Tests\Recipe\Behat;

use Teknoo\Recipe\Ingredient\MergeableInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class IntBag implements MergeableInterface
{
    private int $value;

    public function __construct($value)
    {
        $this->value = (int) $value;
    }

    public static function addValue(IntBag $bag, IntBag $toAdd): void
    {
        $bag->value += $toAdd->value;
    }

    public static function initializeTo10(IntBag $bag): void
    {
        $bag->value = 10;
    }

    public static function increaseValue(IntBag $bag): void
    {
        ++$bag->value;
    }

    public function merge(MergeableInterface $mergeable): MergeableInterface
    {
        $this->value += $mergeable->value;

        return $this;
    }
}
