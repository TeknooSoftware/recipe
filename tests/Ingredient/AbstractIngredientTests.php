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

use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\IngredientBagInterface;
use Teknoo\Recipe\Ingredient\IngredientInterface;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractIngredientTests extends TestCase
{
    /**
     * @return IngredientInterface
     */
    abstract public function buildIngredient(bool $mandatory = true, mixed $default = null): IngredientInterface;

    abstract public function getWorkPlanValid(): array;

    abstract public function getWorkPlanInvalidMissing(): array;

    abstract public function getWorkPlanInvalidNotInstanceOf(): array;

    abstract public function getWorkPlanInjected(): array;

    abstract public function getDefaultValue(): mixed;

    public function testExceptionOnPrepareWhenWorkPlanIsNotAnArray()
    {
        $this->expectException(\TypeError::class);
        $s = new \stdClass();
        $this->buildIngredient()->prepare($s, $this->createMock(RecipeInterface::class));
    }

    public function testExceptionOnPrepareWhenWorkPlanIsNotPromise()
    {
        $this->expectException(\TypeError::class);
        $a = [];
        $this->buildIngredient()->prepare($a, new \stdClass());
    }

    public function testPrepareWithValidPlan()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects(self::never())
            ->method('missing');

        $chef->expects(self::once())
            ->method('updateWorkPlan')
            ->with($this->getWorkPlanInjected())
            ->willReturnSelf();

        $a = $this->getWorkPlanValid();
        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithValidPlanWithBag()
    {
        $chef = $this->createMock(ChefInterface::class);
        $bag = $this->createMock(IngredientBagInterface::class);

        $chef->expects(self::never())
            ->method('missing');

        $chef->expects(self::never())
            ->method('updateWorkPlan')
            ->with($this->getWorkPlanInjected())
            ->willReturnSelf();

        $bag->expects(self::never())
            ->method('updateWorkPlan')
            ->with($this->getWorkPlanInjected())
            ->willReturnSelf();

        $a = $this->getWorkPlanValid();
        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef,
                $bag
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotPresent()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects(self::once())
            ->method('missing');

        $chef->expects(self::never())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        $a = $this->getWorkPlanInvalidMissing();
        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotPresentButNotBandatory()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects(self::never())
            ->method('missing');

        $chef->expects(self::once())
            ->method('updateWorkPlan')
            ->with(['IngName' => $this->getDefaultValue()])
            ->willReturnSelf();

        $a = $this->getWorkPlanInvalidMissing();
        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient(
                mandatory: false,
                default: $this->getDefaultValue(),
            )->prepare(
                $a,
                $chef
            )
        );
    }

    public function testPrepareWithInvalidPlanTheIngredientIsNotOfTheRequiredClass()
    {
        $chef = $this->createMock(ChefInterface::class);

        $chef->expects(self::once())
            ->method('missing');

        $chef->expects(self::never())
            ->method('updateWorkPlan')
            ->willReturnSelf();

        $a = $this->getWorkPlanInvalidNotInstanceOf();
        self::assertInstanceOf(
            IngredientInterface::class,
            $this->buildIngredient()->prepare(
                $a,
                $chef
            )
        );
    }
}
