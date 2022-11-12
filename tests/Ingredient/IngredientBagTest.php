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

namespace Teknoo\Tests\Recipe\Ingredient;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\IngredientBag;
use Teknoo\Recipe\Ingredient\IngredientBagInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Ingredient\IngredientBag
 */
class IngredientBagTest extends TestCase
{
    public function buildBag(): IngredientBag
    {
        return new IngredientBag();
    }

    public function testSetBadName()
    {
        $this->expectException(\TypeError::class);

        $this->buildBag()->set(new \stdClass(), 'foo');
    }

    public function testSet()
    {
        self::assertInstanceOf(
            IngredientBagInterface::class,
            $this->buildBag()->set('foo', new \stdClass())
        );
    }

    public function testUpdateWorkPlanBadChef()
    {
        $this->expectException(\TypeError::class);

        $this->buildBag()->updateWorkPlan(new \stdClass());
    }

    public function testUpdateWorkPlan()
    {
        self::assertInstanceOf(
            IngredientBagInterface::class,
            $this->buildBag()->updateWorkPlan($this->createMock(ChefInterface::class))
        );
    }
}
