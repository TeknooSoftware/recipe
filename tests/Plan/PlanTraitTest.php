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

use DateTimeInterface;
use DateTimeZone;
use DateTime;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Plan\BasePlanTrait;
use Teknoo\Recipe\PlanInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
final class PlanTraitTest extends TestCase
{
    use BasePlanTestTrait;

    public function buildPlan(): PlanInterface
    {
        return new class ($this->createStub(RecipeInterface::class)) implements PlanInterface {
            use BasePlanTrait;

            public function __construct(RecipeInterface $recipe)
            {
                $this->fill($recipe);
            }

            protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
            {
                $recipe = $recipe->require(
                    new Ingredient(DateTimeInterface::class, 'date')
                );

                $recipe = $recipe->cook(
                    function (DateTime $date, ChefInterface $chef): void {
                        $date = $date->setTimezone(new DateTimeZone('UTC'));

                        $chef->continue(['date' => $date]);
                    },
                    'convertToUTC'
                );

                return $recipe->cook(
                    function (DateTime $date, ChefInterface $chef): void {
                        $immutable = DateTimeImmutable::createFromMutable($date);

                        $chef->finish($immutable);
                    },
                    'immutableDate'
                );
            }
        };
    }
}
