<?php

declare(strict_types=1);

namespace Acme;

use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Recipe;

require __DIR__ . '/../vendor/autoload.php';

$recipe = new Recipe();

$recipe = $recipe->cook(
    static function (ChefInterface $chef): void {
        $chef->updateWorkPlan([
            'toCall' => static function (): void {
                echo 'dynamic closure called' . PHP_EOL;
            },
        ]);
    },
    'createCall',
);

$recipe = $recipe->cook(
    new DynamicBowl('toCall', false),
    'dynamicCall',
);

$chef = new Chef;
$chef->read($recipe);
$chef->process([]);
