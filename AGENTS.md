# Project: Recipe (teknoo/recipe)

## Overview
A PHP library, inspired by cooking, to write dynamic algorithms called *recipes*: an ordered list of steps
(callables, "middlewares"), executed by a *chef* when a set of required *ingredients* is available in a *workplan*.
Recipes follow the #east programming style and are designed to be configured through a dependency injection
container. Steps can be reordered, inserted relatively to other steps, executed in PHP Fibers, delegated to
sub-recipes or resolved at runtime from the workplan.

## Core concepts (as implemented in `src/`)

### Recipe (`Recipe`, `RecipeInterface`, `BaseRecipeInterface`)
The immutable definition of an algorithm. Every builder method returns a **new instance** (the original is never
modified) : `require()` adds a required ingredient, `cook()` adds a step, `execute()` adds a sub-recipe (or a plan) as
a step, `onError()` adds an error handler, `given()` sets the expected dish. Always use the returned instance.
A recipe is a stated class (teknoo/states) : `Draft` while it can be completed, `Written` once compiled by
`train()`. `train()` compiles a clone, so the instance you keep stays a `Draft`.
Steps are grouped by position (an integer, explicit or auto incremented) and executed by ascending position, then by
insertion order inside a position. `cook()`/`execute()` accept an explicit `int` position or a
`RecipeRelativePositionEnum::Before` / `After` with the name of an existing step (`$offsetStepName`). A step relative
to an unknown step is appended at the end. Duplicated step names are suffixed (`step`, `step1`, ...).

### Chef (`Chef`, `ChefInterface`)
The executor of a `BaseRecipeInterface` (a `Recipe` or a `Plan`). Stated class : `Free` (untrained), `Trained`
(has read a recipe, can `process()` a workplan), `Cooking` (currently executing). `process($workplan)` prepares the
ingredients, runs the steps in order, then cleans the workplan (also when an exception escapes).
From a step, the chef API allows to : `continue($with, $nextStep)` (add ingredients and go on, optionally jump to a
named step), `updateWorkPlan()`, `merge()` (for `MergeableInterface` values), `cleanWorkPlan()`, `finish($result)`
(stop and validate the dish), `error($throwable)` (call the error handlers), `interruptCooking()` (stop without dish
validation), `stopErrorReporting()` (a sub-chef does not report the error to its top chef), `reserveAndBegin()`
(start a sub-chef for a sub-recipe). A chef can be reused for several cookings and can be cloned.

### Workplan
The associative array of ingredients available during a cooking (`name => value`). It is copied to sub-chefs.
Error handlers receive the caught exception under the key `exception`. A `null` value is considered as absent.

### Ingredient (`Ingredient`, `IngredientWithCondition`, `IngredientBag`, `IngredientInterface`)
A **requirement** checked before the cooking (not a value) : a type (scalar type name like `string`, `int`,
`iterable`, `callable`, or a class / interface / enum name), an optional name, a normalized name, an optional
normalizer callback, a mandatory flag and a default value. Object requirements are checked with `is_a()` (a class
name string is accepted), backed enums are automatically built from scalars. An unnamed object ingredient is found
from its type whatever its key. `IngredientWithCondition` is checked only when a condition callback returns true.
Values in the workplan may implement `MergeableInterface` (merged by `ChefInterface::merge()`) or
`TransformableInterface` (transformed before the injection into a step, see the `Transform` attribute).

### Bowl (`Bowl`, `FiberBowl`, `DynamicBowl`, `DynamicFiberBowl`, `RecipeBowl`, `FiberRecipeBowl`, `BowlInterface`)
The immutable container of **one step** : it maps the ingredients of the workplan to the parameters of the step's
callable and executes it. It does not hold any state of the cooking. `Bowl` runs the callable directly, `FiberBowl`
in a new `Fiber` registered into the cooking supervisor, `DynamicBowl` / `DynamicFiberBowl` fetch the callable
itself from the workplan at execution, `RecipeBowl` / `FiberRecipeBowl` run a sub-recipe with a new sub-chef
(optionally repeated : a fixed count or a callable receiving `counter` and `bowl`, calling `$bowl->stopLooping()`).
Parameters are resolved, in this order :
1. reserved instances by type : `ChefInterface`, `Fiber` (fiber bowls only), `CookingSupervisorInterface`,
2. the mapping given to `cook()` (`$with`) : another name, a list of names (first found), or a fixed `Value`,
3. the parameter name as key of the workplan,
4. the class name of the parameter as key of the workplan,
5. an instance of the parameter's type in the workplan (also a `TransformableInterface` instance of the class given
   to `#[Transform]`, transformed before injection),
6. the special name `_methodName` (the name of the step),
7. the default value of the parameter, else a `BadMethodCallException` is thrown.
The `#[Transform(className, transformer)]` attribute on a parameter transforms the ingredient before injection
(`TransformableInterface::transform()` and/or a transformer callable allowed in an attribute : a function name or
an array `[Class::class, 'method']`, and a first-class callable `Class::method(...)` since PHP 8.5). Callables'
parameters are cached by callable (`Class::method`, function name, or file:line for closures).

### Dish (`DishClass`, `AbstractDishClass`, `DishInterface`)
The **validator of the result** of a recipe, called only when a step calls `ChefInterface::finish($result)`. It
checks the result (an instance of the expected class for `DishClass`) and calls the `success()` or `fail()` callback
of its promise. Nothing is returned by the chef.

### Promise (`Promise`, `FiberPromise`, `WrappedOneCalledPromise`, `PromiseInterface`)
The #east way to get a result without a return value : a pair of callbacks `onSuccess` / `onFail`, called
**synchronously** by `success()` / `fail()`. A promise can be called once (unless `allowReuse()`), chained with
`next()` (which returns a new promise), and exposes the result of its callback with `fetchResult()` /
`fetchResultIfCalled()` for non-east code. `FiberPromise` runs the callback in a fiber. `WrappedOneCalledPromise` is
an internal wrapper created by `next()`.

### Plan (`PlanInterface`, `EditablePlanInterface`, `BasePlanTrait`, `EditablePlanTrait`, `Plan\Step`)
A **factory of recipe**, to define a reusable algorithm as a class (typed, injectable in a container). A plan
populates a recipe (`populateRecipe()`), can be `fill()`ed with a prefilled recipe, adds default ingredients to
the workplan (`addToWorkplan()`, the plan itself is available under the key `PlanInterface::class`). An editable
plan accepts additional steps (`add($step, $position)`, `Plan\Step` to pass a mapping) and error handlers
(`addErrorHandler()`) after its definition. A chef reads a plan like a recipe.

### CookingSupervisor (`CookingSupervisor`, `CookingSupervisorInterface`, `CookingSupervisor\FiberIterator`)
The scheduler of the fibers created by fiber bowls, and of the supervisors of sub-chefs. The chef never drives it :
a step must call `switch()` (resume the next suspended fiber), `loop()` (resume each fiber once), `finish()` (loop
until all fibers are terminated), `throw()` (resume the next fiber with an exception). Fibers are resumed in the
order of their registration, terminated (or never started) fibers are forgotten.

### Value
A wrapper to inject a fixed value into a step's parameter through the mapping of `cook()`
(`['param' => new Value('fixed')]`).

## Security notes
- `DynamicBowl` / `DynamicFiberBowl` execute a callable **read from the workplan**. Never map their key to a value
  controlled by an end user.
- An object ingredient accepts a class name as string value. Do not feed such an ingredient with user input.

## Technical stack
- PHP `^8.4` (`declare(strict_types=1)` everywhere, readonly properties, enums, attributes, fibers). PHP 8.5 only
  features are reserved for the next major release.
- Dependencies : `teknoo/immutable` `^3.0.22` (immutability contract), `teknoo/states` `^7.1.11` (stated classes
  `Recipe` and `Chef`).
- Tests : PHPUnit 13 (`tests/`, files `*Test.php`; shared abstract suites are `*Tests.php` / `*TestTrait.php`;
  `tests/Support/` holds fixtures also analysed by PHPStan) and Behat 4 (`features/*.feature`, context
  `tests/Behat/FeatureContext.php`, helpers `tests/Behat/IntBag.php` and `StringObject.php`).
- QA : PHPStan level max (`phpstan.neon`, `src/` and `tests/Support/`), PHP_CodeSniffer **PSR-12** on `src/`,
  `composer audit`.
- CI : GitLab (`.gitlab-ci.yml`), PHP 8.4 and 8.5 with lowest and latest dependencies. GitHub is a mirror.
- Examples : `demo/*.php`. Documentation : `documentation/README.md`, `README.md`, `CHANGELOG.md`.

## Commands (Makefile)
- `make test` : PHPUnit with code coverage (**requires the Xdebug extension**, loaded by the target itself) then
  Behat.
- `make qa` : `php -l` on `src/`, PHPStan, PHPCS (PSR-12), `composer audit`. `make qa-offline` : the same without
  the audit.
- `make depend` : `composer update` (`DEPENDENCIES=lowest` for `--prefer-lowest`).
- **Warning** : a bare `make` runs `clean` then `depend`, it **deletes `vendor/`** before reinstalling.
- Faster loops during development : `vendor/bin/phpunit --no-coverage`, `php vendor/bin/behat`,
  `vendor/bin/phpstan analyse`, `vendor/bin/phpcs --standard=PSR12 --extensions=php src/`.

## Development rules
- Coding standard : PSR-12, `declare(strict_types=1);` in every file, the BSD license header of the project in every
  file (see any file in `src/` or `tests/`).
- The public API and the observable behaviour must not be broken in a minor or patch release.
- **Every change of code must come with new tests, in PHPUnit (`tests/`) and in Behat (`features/`)**, to prevent a
  future regression. An unconfirmed issue must first be reproduced by a failing test.
- **Never modify an existing test** (PHPUnit method, shared abstract suite, feature file or existing step
  definition). An existing test failing after a change means a regression to fix in the code, not a test to adapt.
  Only add : new test files, new feature files or scenarios, new step definitions and helpers appended to
  `FeatureContext.php`. If an existing test is really wrong (a false negative encoding a bug, or a test coupled
  to internals that a fix must change), **ask the maintainer before touching it**.
- **One commit per bug fix, optimization or document**, self-contained : the code change, its new PHPUnit and Behat
  tests and its line in `CHANGELOG.md` (section of the next release). Before each commit, `make test` and
  `make qa` must pass. Commit messages are short imperative sentences (`Fix ...`, `Remove ...`).
- Code coverage : the PHPUnit suite covers 100% of `src/`, keep it that way (checked in the coverage report of
  `make test`).
- Never remove or rewrite code that looks useless, dead or redundant (iterations by reference, caches, checks,
  configuration entries) on your own initiative : it is usually intentional. List it and ask the maintainer first.
- An optimization must be measured (benchmark before/after the change, same scenarios) and must not change any
  behavior. A cleanup is not an optimization.
- Tests of a bug fix must assert the final result of the recipe (the dish, the workplan or the error message), not
  only that no exception was thrown.
- Behat scenarios must end with a `Then` assertion : the `It starts cooking ...` steps catch every `Throwable`.
- Code requiring a syntax newer than the minimum PHP version (like the first-class callable in an attribute of
  PHP 8.5) lives in a dedicated fixture (`tests/Php85/`, never loaded on older versions), used by PHPUnit tests
  marked `#[RequiresPhp('>= 8.5.0')]` and by Behat scenarios tagged `@php85` (filtered by `make test` on older
  versions).
- `CHANGELOG.md` is the reference of behaviour changes, keep it up to date in each commit.

## Workflow
1. Create a new branch for a hotfix or a feature (never work on `master`).
2. Reproduce the issue with a failing test (PHPUnit and Behat), then implement the change.
3. Run `make test` and `make qa`, commit (one commit per fix, with its tests and its CHANGELOG line).
4. Submit a merge request / pull request.
