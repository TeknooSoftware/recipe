Teknoo Software - Recipe library
================================

[![Build Status](https://travis-ci.org/TeknooSoftware/recipe.svg?branch=master)](https://travis-ci.org/TeknooSoftware/recipe) [![Build Status](https://travis-ci.org/TeknooSoftware/recipe.svg?branch=master)](https://travis-ci.org/TeknooSoftware/recipe)

Inspired by cooking, allows the creation of dynamic algorithm, called here recipe,
following the #east programming and using middleware, configurable via DI or any configuration,
if a set of conditions (ingredients) are available.

Example
-------

    <?php

    use Teknoo\Recipe\Dish\DishClass;
    use Teknoo\Recipe\Ingredient\Ingredient;
    use Teknoo\Recipe\Recipe;
    use Teknoo\Recipe\Chef;
    use Teknoo\Recipe\ChefInterface;
    use Teknoo\Recipe\Promise\Promise;

    require 'vendor/autoload.php';

    $recipe = new Recipe();

    $recipe = $recipe->require(
        new Ingredient(\DateTime::class, 'date')
    );

    $recipe = $recipe->cook(
        function (\DateTime $date, ChefInterface $chef) {
            $date = $date->setTimezone(new \DateTimeZone('UTC'));

            $chef->continue(['date' => $date]);
        },
        'convertToUTC'
    );

    $recipe = $recipe->cook(
        function (\DateTime $date, ChefInterface $chef) {
            $immutable = \DateTimeImmutable::createFromMutable($date);

            $chef->finish($immutable);
        },
        'immutableDate'
    );

    $output = '';
    $recipe = $recipe->given(
        new DishClass(
            \DateTimeImmutable::class,
            new Promise(
                function (\DateTimeImmutable $immutable) use (&$output) {
                    $output = $immutable->format('Y-m-d H:i:s T');
                },
                function (\Throwable $error) use (&$output) {
                    $output = $error->getMessage();
                }
            )
        )
    );

    $chef = new Chef;
    $chef->read($recipe);
    $chef->process(['date' => new \DateTime('2017-12-25 00:00:00', new \DateTimeZone('Europe/Paris'))]);
    echo $output.PHP_EOL;
    //Output : 2017-12-24 23:00:00 UTC

Support this project
---------------------

This project is free and will remain free, but it is developed on my personal time. 
If you like it and help me maintain it and evolve it, don't hesitate to support me on [Patreon](https://patreon.com/teknoo_software).
Thanks :) Richard. 

Installation & Requirements
---------------------------
To install this library with composer, run this command :

    composer require teknoo/recipe

This library requires :

    * PHP 7.4+
    * A PHP autoloader (Composer is recommended)
    * Teknoo/Immutable.
    * Teknoo/States.

News from Teknoo Recipe 2.0
----------------------------

This library requires PHP 7.4 or newer. Some change causes bc breaks :

- PHP 7.4 is the minimum required
- Most methods have been updated to include type hints where applicable. Please check your extension points to make sure the function signatures are correct.
- Switch to typed properties
_ All files use strict typing. Please make sure to not rely on type coercion.
- Replace array_merge by "..." operators for integer indexed arrays
- Remove some PHP useless DockBlocks
- Enable PHPStan in QA Tools and disable PHPMd
- Enable PHPStan extension dedicated to support Stated classes

Credits
-------
Richard Déloge - <richarddeloge@gmail.com> - Lead developer.
Teknoo Software - <https://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge.
Teknoo Software's goals : Provide to our partners and to the community a set of high quality services or software,
 sharing knowledge and skills.

License
-------
Recipe is licensed under the MIT License - see the licenses folder for details

Contribute :)
-------------

You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
