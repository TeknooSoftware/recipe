Teknoo Software - Recipe library
================================

[![Latest Stable Version](https://poser.pugx.org/teknoo/recipe/v/stable)](https://packagist.org/packages/teknoo/recipe)
[![Latest Unstable Version](https://poser.pugx.org/teknoo/recipe/v/unstable)](https://packagist.org/packages/teknoo/recipe)
[![Total Downloads](https://poser.pugx.org/teknoo/recipe/downloads)](https://packagist.org/packages/teknoo/recipe)
[![License](https://poser.pugx.org/teknoo/recipe/license)](https://packagist.org/packages/teknoo/recipe)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat)](https://github.com/phpstan/phpstan)

Inspired by cooking, allows the creation of dynamic algorithm, called here recipe,
following the #east programming and using middleware, configurable via DI or any configuration,
if a set of conditions (ingredients) are available.

A complete documentation is available in [documentation/README.md](documentation/README.md)

Quick Example
-------------

    <?php
    
    declare(strict_types=1);
    
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
        function (\DateTime $date, ChefInterface $chef): void {
            $date = $date->setTimezone(new \DateTimeZone('UTC'));
    
            $chef->continue(['date' => $date]);
        },
        'convertToUTC'
    );
    
    $recipe = $recipe->cook(
        function (\DateTime $date, ChefInterface $chef): void {
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
                function (\DateTimeImmutable $immutable) use (&$output): void {
                    $output = $immutable->format('Y-m-d H:i:s T');
                },
                function (\Throwable $error) use (&$output): void {
                    $output = $error->getMessage();
                }
            )
        )
    );
    
    $chef = new Chef;
    $chef->read($recipe);
    $chef->process(['date' => new \DateTime('2020-06-27 00:00:00', new \DateTimeZone('Europe/Paris'))]);

    //Show : 2020-06-26 22:00:00 UTC
    echo $output.PHP_EOL;

Others examples are available into demo

A complete documentation is available in [documentation/README.md](documentation/README.md)

Support this project
---------------------
This project is free and will remain free. It is fully supported by the activities of the EIRL.
If you like it and help me maintain it and evolve it, don't hesitate to support me on
[Patreon](https://patreon.com/teknoo_software) or [Github](https://github.com/sponsors/TeknooSoftware).

Thanks :) Richard.

Credits
-------
EIRL Richard Déloge - <https://deloge.io> - Lead developer.
SASU Teknoo Software - <https://teknoo.software>

About Teknoo Software
---------------------
**Teknoo Software** is a PHP software editor, founded by Richard Déloge, as part of EIRL Richard Déloge.
Teknoo Software's goals : Provide to our partners and to the community a set of high quality services or software,
sharing knowledge and skills.

License
-------
Recipe is licensed under the MIT License - see the licenses folder for details.

Installation & Requirements
---------------------------
To install this library with composer, run this command :

    composer require teknoo/recipe

This library requires :

    * PHP 8.1+
    * A PHP autoloader (Composer is recommended)
    * Teknoo/Immutable.
    * Teknoo/States.

News from Teknoo Recipe 4.0
----------------------------
This library requires PHP 8.1 or newer. Some change causes bc breaks.

- Use readonly properties for immutables objects.
- Constant `BowlInterface::METHOD_NAME` is final.
- Support `Fiber` into `Promise`.
- Support `Fiber` into `Bowl` and `DynamicBowl` thanks to dynamics classes :
  - callable will be automatically wrapped by a fiber,
  - the fiber object will be available as parameter for bowls.
- Add `Fiber` support to RecipeBowl, also in a dedicated class `FiberRecipeBowl`.
  The Fiber instance is also passed into workplan.
- Add a `CookingSupervisorInterface` and its default implementation `CookingSupervisor` to manage
  Bowls Fibers executions and loop on each active fiber.
  `CookingSupervisor` are also available as parameter for bowls.

News from Teknoo Recipe 3.1
----------------------------
This library requires PHP 8.0 or newer.

- Add `MergeableInterface` and `ChefInterface::merge()` to allow merge ingredient instead of replace it with `updateWorkplan`
  without fetch it into step.
- Add `TransformableInterface` and attribute `Transform` to allow transform an ingredient before to put it into the bowl

News from Teknoo Recipe 3.0
----------------------------
This library requires PHP 8.0 or newer. Some change causes bc breaks.

- Promise immutable check is performed before var assignment
- Some optimisations on array functions to limit O(n)
- Subs chefs executing an embedded recipe inherit also of error handler with the workplan but can be changed without impact
  the original handler in the main chef.
- Subs chefs call also theirs top chef's callErrors method on error
- Add `interruptCooking` method to stop execution of chef without execute finals steps (dish validation or error handlers)
- Add `stopErrorReporting` method to stop error reporting to top chef

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

Contribute :)
-------------
You are welcome to contribute to this project. [Fork it on Github](CONTRIBUTING.md)
