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

namespace Teknoo\Tests\Recipe\Ingredient;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\IngredientBag;
use Teknoo\Recipe\Ingredient\IngredientBagInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(IngredientBag::class)]
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
