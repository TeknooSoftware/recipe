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

namespace Teknoo\Tests\Recipe\Cookbook;

use PHPUnit\Framework\Attributes\CoversTrait;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\RecipeInterface;

/**
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversTrait(BaseCookbookTrait::class)]
class CoobookTraitTest extends TestCase
{
    use BaseCookbookTestTrait;

    public function buildCookbook(): CookbookInterface
    {
        return new class ($this->createMock(RecipeInterface::class)) implements CookbookInterface {
            use BaseCookbookTrait;

            public function __construct(RecipeInterface $recipe)
            {
                $this->fill($recipe);
            }

            protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
            {
                $recipe = $recipe->require(
                    new Ingredient(\DateTimeInterface::class, 'date')
                );

                $recipe = $recipe->cook(
                    function (\DateTime $date, ChefInterface $chef): void {
                        $date = $date->setTimezone(new \DateTimeZone('UTC'));

                        $chef->continue(['date' => $date]);
                    },
                    'convertToUTC'
                );

                return $recipe->cook(
                    function (\DateTime $date, ChefInterface $chef): void {
                        $immutable = \DateTimeImmutable::createFromMutable($date);

                        $chef->finish($immutable);
                    },
                    'immutableDate'
                );
            }
        };
    }
}
