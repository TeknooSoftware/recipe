<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
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

namespace Teknoo\Tests\Recipe\Behat;

use Teknoo\Recipe\Ingredient\MergeableInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class IntBag implements MergeableInterface
{
    /**
     * @var int
     */
    private $value;

    public function __construct($value)
    {
        $this->value = (int) $value;
    }

    public static function addValue(IntBag $bag, IntBag $toAdd)
    {
        $bag->value += $toAdd->value;
    }

    public static function initializeTo10(IntBag $bag)
    {
        $bag->value = 10;
    }

    public static function increaseValue(IntBag $bag)
    {
        $bag->value++;
    }

    public function merge(MergeableInterface $mergeable): MergeableInterface
    {
        $this->value += $mergeable->value;

        return $this;
    }
}
