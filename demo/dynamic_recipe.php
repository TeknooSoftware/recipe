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
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

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
