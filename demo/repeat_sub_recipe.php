<?php

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
