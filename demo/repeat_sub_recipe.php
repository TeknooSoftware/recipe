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

namespace Acme2;

use Teknoo\Recipe\Bowl\RecipeBowl;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Recipe\Ingredient\TransformableInterface;
use Teknoo\Recipe\Recipe;

require __DIR__ . '/../vendor/autoload.php';

class IntValue implements TransformableInterface
{
    public function __construct(
        public int $value = 0,
    ) {
    }

    public function transform(): mixed
    {
        return (int) $this->value;
    }
}

$subRecipe = new Recipe();

$subRecipe = $subRecipe->cook(
    static function (IntValue $value): void {
        $value->value++;
    },
    'increaseCounter'
);

$subRecipe = $subRecipe->cook(
    static function (): void {
        echo "I MUST NOT WRITE ALL OVER THE WALLS" . PHP_EOL;
    },
    'displayInformation'
);

$recipe = new Recipe();

$recipe = $recipe->cook(
    static function (ChefInterface $chef): void {
        $chef->updateWorkPlan([
            IntValue::class => new IntValue(0),
        ]);
    },
    'createArray'
);

$recipe = $recipe->execute(
    $subRecipe,
    'subrecipeCall',
    static function (#[Transform(IntValue::class)] int $value, RecipeBowl $bowl): void {
        if ($value > 5) {
            $bowl->stopLooping();
        }
    }
);

$chef = new Chef;
$chef->read($recipe);
$chef->process([]);

/**
 * Will show :
 * I MUST NOT WRITE ALL OVER THE WALLS
 *  I MUST NOT WRITE ALL OVER THE WALLS
 *  I MUST NOT WRITE ALL OVER THE WALLS
 *  I MUST NOT WRITE ALL OVER THE WALLS
 *  I MUST NOT WRITE ALL OVER THE WALLS
 *  I MUST NOT WRITE ALL OVER THE WALLS
 */
