<?php

declare(strict_types=1);

namespace Acme;

use DateTime;
use DateTimeImmutable;
use Teknoo\Recipe\Dish\DishClass;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Promise\Promise;
use Throwable;

require __DIR__ . '/../vendor/autoload.php';

$recipe = new Recipe();

$recipe = $recipe->require(
    new Ingredient(DateTime::class, 'date')
);

$recipe = $recipe->cook(
    function (DateTime $date, ChefInterface $chef): void {
        $date = $date->setTimezone(new \DateTimeZone('UTC'));

        $chef->continue(['date' => $date]);
    },
    'convertToUTC',
);

$recipe = $recipe->cook(
    function (DateTime $date, ChefInterface $chef): void {
        $immutable = DateTimeImmutable::createFromMutable($date);

        $chef->finish($immutable);
    },
    'immutableDate',
);

$output = '';
$recipe = $recipe->given(
    new DishClass(
        DateTimeImmutable::class,
        new Promise(
            function (DateTimeImmutable $immutable) use (&$output): void {
                $output = $immutable->format('Y-m-d H:i:s T');
            },
            function (Throwable $error) use (&$output): void {
                $output = $error->getMessage();
            }
        )
    )
);

$chef = new Chef;
$chef->read($recipe);
$chef->process(['date' => new DateTime('2020-06-27 00:00:00', new \DateTimeZone('Europe/Paris'))]);

//Show : 2020-06-26 22:00:00 UTC
echo $output.PHP_EOL;