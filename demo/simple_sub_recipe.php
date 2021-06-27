<?php

declare(strict_types=1);

namespace Acme;

use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Recipe;

use function sort;

require __DIR__ . '/../vendor/autoload.php';

$subRecipe = new Recipe();

$subRecipe = $subRecipe->cook(
    static function (ChefInterface $chef, array $values): void {
        sort($values);

        $chef->updateWorkPlan(['values' => $values]);
    },
    'sort',
);

$subRecipe = $subRecipe->cook(
    static function (array $values): void {
        print_r($values);
    },
    'displayInSub',
);

$recipe = new Recipe();

$recipe = $recipe->cook(
    static function (ChefInterface $chef): void {
        $chef->updateWorkPlan([
            'values' => [5, 3, 7, 1, 10]
        ]);
    },
    'createArray',
);

$recipe = $recipe->execute(
    $subRecipe,
    'subrecipeCall',
);

$recipe = $recipe->cook(
    static function (array $values): void {
        print_r($values);
    },
    'displayInMain',
);

$chef = new Chef;
$chef->read($recipe);
$chef->process([]);

/*
 * Will show
 * Array
 *  (
 *      [0] => 1
 *      [1] => 3
 *      [2] => 5
 *      [3] => 7
 *      [4] => 10
 *  )
 *  Array
 *  (
 *      [0] => 5
 *      [1] => 3
 *      [2] => 7
 *      [3] => 1
 *      [4] => 10
 *  )
 */
