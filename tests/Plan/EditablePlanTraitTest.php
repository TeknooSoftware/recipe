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

use Throwable;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Plan\Step;
use Teknoo\Recipe\PlanInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
final class EditablePlanTraitTest extends TestCase
{
    use EditablePlanTestTrait;

    public function buildPlan(): EditablePlanInterface
    {
        $plan = new class ($this->createStub(RecipeInterface::class)) implements EditablePlanInterface {
            use EditablePlanTrait;

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
                    action: function (DateTime $date, ChefInterface $chef): void {
                        $date = $date->setTimezone(new DateTimeZone('UTC'));

                        $chef->continue(['date' => $date]);
                    },
                    name: 'convertToUTC',
                    position: 1,
                );

                return $recipe->cook(
                    action: function (DateTime $date, ChefInterface $chef): void {
                        $immutable = DateTimeImmutable::createFromMutable($date);

                        $chef->finish($immutable);
                    },
                    name: 'immutableDate',
                    position: 6
                );
            }
        };

        $plan->add(
            new Bowl(
                function (DateTime $date, ChefInterface $chef): void {
                    $date = $date->modify('+1 day');

                    $chef->continue(['date' => $date]);
                },
                [],
            ),
            2,
        );

        $plan->add(
            function (DateTime $date, ChefInterface $chef): void {
                $date = $date->modify('+1 month');

                $chef->continue(['date' => $date]);
            },
            3,
        );

        $plan->add(
            new Step(
                function (DateTime $aDate, ChefInterface $chef): void {
                    $aDate = $aDate->modify('+1 year');

                    $chef->continue(['date' => $aDate]);
                },
                ['date' => 'aDate'],
            ),
            4,
        );

        $plan->add(
            new Step(
                [new DateTime(), 'modify'],
                ['date' => 'aDate'],
            ),
            4,
        );

        $plan->addErrorHandler(
            fn (Throwable $e) => \var_dump($e->getMessage()),
        );

        return $plan;
    }
}
