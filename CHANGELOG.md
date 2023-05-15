# Teknoo Software - Recipe - Change Log

## [4.2.7] - 2023-05-15
### Stable Release
- Update dev lib requirements
- Update copyrights

## [4.2.6] - 2023-04-16
### Stable Release
- Update dev lib requirements
- Support PHPUnit 10.1+
- Migrate phpunit.xml

## [4.2.5] - 2023-03-11
### Stable Release
- Q\A

## [4.2.4] - 2023-03-10
### Stable Release
- Q\A

## [4.2.3] - 2023-02-11
### Stable Release
- Remove phpcpd and upgrade phpunit.xml

## [4.2.2] - 2023-02-03
### Stable Release
- Update dev libs to support PHPUnit 10 and remove unused phploc

## [4.2.1] - 2022-12-15
### Stable Release
- QA Fixes

## [4.2.0] - 2022-10-14
### Stable Release
- Add `bool $autoCall = false` to `PromiseInterface::next` to call automatically a next
 promise if it was already called in the promise (the next promise is wrapped in a proxy
 promise to allow call only a one time the method `success`). If the promise is automatically
 called, the result of the current promise is passed to the next promise.
- If a promise A is added as next to a promise B, all anothers promises added as next to B will be
 redirect to A (and recursively to the last added promise). the writing of chains of promises is thus facilitated.
- Fix a bug, if `fetchResult` return the result of last promise instead the first.

## [4.1.5] - 2022-10-06
### Stable Release
- Support of PHPStan 1.8.8+

## [4.1.4] - 2022-06-17
### Stable Release
- Clean code and test thanks to Rector
- Update libs requirements

## [4.1.3] - 2022-04-14
### Stable Release
- Complete errors messages in bowl when missed parameter's value or bad parameter definition
  to help to debug

## [4.1.2] - 2022-03-09
### Stable Release
- Add templates on `PromiseInterface` to improve QA

## [4.1.1] - 2022-03-08
### Stable Release
- Require Immutable 3.0.1 or later

## [4.1.0] - 2022-03-03
### Stable Release
- Add transformer option to `#[Trasnform]` to use a callable instead an object
 with the interface `TransformableInterface`. 
 The callable can also return a Transformable object.

## [4.0.1] - 2022-02-27
### Stable Release
- Fix issue with `RecipeBowl` and `FiberRecipeBowl`, workplan was not updated after reserving

## [4.0.0] - 2022-02-10
### Stable Release
- Remove PHP 8.0 support.
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

## [3.4.5] - 2022-02-09
### Stable Release
- Support Immutable 3.0
- Support State 6.0

## [3.4.4] - 2021-12-12
### Stable Release
- Remove unused QA tool

## [3.4.3] - 2021-12-03
### Stable Release
- Fix some deprecated with PHP 8.1

## [3.4.2] - 2021-11-16
### Stable Release
- QA 
- Prevent bug with nullable attribute
- Prevent bug with Reflection

## [3.4.1] - 2021-11-11
### Stable Release
- Update QA tool with PHPStan 1.0
- Update State lib
- Fix QA issues

## [3.4.0] - 2021-08-29
### Stable Release
- `Coobook Aware` : Cookbook written with the `BaseCookbookTrait` add them (if it is not already defined) in
  the default workplan to help developers to write recipe's step aware of the cookbook.

## [3.3.0] - 2021-08-30
### Stable Release
- Add `Promise::fetchResult`, To get the returned value by the callback on the promise (Can be null if the callback 
  return nothing). *(Not east compliant, but useful to integrate east code with an non-east code).*

## [3.2.1] - 2021-08-19
### Stable Release
- QA, Fix PHPDoc.

## [3.2.0] - 2021-08-12
### Stable Release
- Import from East Foundation, Next promise behavior in Recipe' promise.
- Promise will also pass as last parameters a promise to chain in the current promise. 
  If any following promise has been defined, an empty promise is passed.
- This new behavior is disabled by default,  to enable it, create a promise with parameter $allowNext at true.

## [3.1.2] - 2021-06-27
### Stable Release
- Fix PhpStan

## [3.1.1] - 2021-06-27
### Stable Release
- Update documents and dev libs requirements

## [3.1.0] - 2021-06-18
### Stable Release
- Add `MergeableInterface` and `ChefInterface::merge()` to allow merge ingredient instead of replace it with `updateWorkplan`
  without fetch it into step.
- Add `TransformableInterface` and attribute `Transform` to allow transform an ingredient before to put it into the bowl

## [3.0.7] - 2021-06-18
### Stable Release
- Remove usage of array_merge into BaseCookbookTrait

## [3.0.6] - 2021-05-31
### Stable Release
- Minor version about libs requirements

## [3.0.5] - 2021-05-27
### Stable Release
- Add `stopErrorReporting` method to stop error reporting to top chef
- Fix behavior of chef when a recipe is terminated

## [3.0.4] - 2021-05-27
### Stable Release
- Subs chefs call also theirs top chef's callErrors method on error
- Add `interruptCooking` method to stop execution of chef without execute finals steps (dish validation or error handlers)

## [3.0.3] - 2021-05-25
### Stable Release
- Subs chefs executing an embedded recipe inherit also of error handler with the workplan but can be changed without impact
  the original handler in the main chef.

## [3.0.2] - 2021-04-28
### Stable Release
- Some optimisations on array functions to limit O(n)

## [3.0.1] - 2021-03-24
### Stable Release
- Promise immutable check is performed before var assignment

## [3.0.0] - 2021-03-20
### Stable Release
- Migrate to PHP 8.0
- QA
- Fix license header

## [2.4.0] - 2021-02-16
### Stable Release
- Add `ChefInterface::cleanWorkPlan` to remove some ingredients from workplan

## [2.3.3] - 2021-02-12
### Stable Release
- Bowl will be select parameters, by priority, on mapping, on name, on type (if class name is used as key in workplan),
  and finaly on instance types
  
## [2.3.2] - 2021-01-23
### Optimisation Release
- Add IngredientBag to limit use of array_merge
- Improve Reflection use and cache in Bowl

## [2.3.1] - 2021-01-23
### Stable Release
- Fix recipe compiling lost step's name and allow GoTo.
- Change fix recipe compiling when several steps share the same name in 1.1.1

## [2.3.0] - 2021-01-20
### Stable Release
- Add Chef::error()

## [2.2.2] - 2021-01-20
### Stable Release
- Fix Default Workplan and merge workplans when chef start a subrecipe

## [2.2.1] - 2021-01-18
### Stable Release
- Add BaseCookbookTrait::addToWorkplan
  
## [2.2.0] - 2021-01-17
### Stable Release
- Add BaseCookbookTrait to implement quickly a cookbook and manage a shared recipe without implement all methods defined in
  the CookbookInterface

## [2.1.3] - 2020-12-03
### Stable Release
- Official Support of PHP8

## [2.1.2] - 2020-10-25
### Stable Release
- Require Teknoo/States ^4.1.3

## [2.1.1] - 2020-10-12
### Stable Release
- Prepare library to support also PHP8.
- Remove deprecations from PHP8.
- Support Union type in PHP8.

## [2.1.0] - 2020-10-04
### Stable Release
Add CookbookInterface behavior to provide to developer a new way to define recipe to inject into Chef
Chef support also Cookbook for new recipe or a new sub recipe
Create a base interface BaseRecipeInterface for RecipeInterface and CookingBookInterface
Migrate from RecipeInterface to BaseRecipeInterface provide all method needed in execution of recipe (train, prepare and validate)
Migrate RecipeBowl to use BaseRecipeInterface instead of RecipeInterface
Migrate Chef to use BaseRecipeInterface instead of RecipeInterface
Mapping in Bowl can be map to an array of string, instead a string to map several possibilities in the workplan (stop on first found occurence)

## [2.1.0-beta5] - 2020-10-03
### Beta Release
Fix BaseRecipeInterface doc

## [2.1.0-beta4] - 2020-10-03
### Beta Release
Fix Chef constructor to use BaseRecipeInterface instead of RecipeInterface

## [2.1.0-beta3] - 2020-10-03
### Beta Release
Create a base interface BaseRecipeInterface for RecipeInterface and CookingBookInterface
Migrate from RecipeInterface to BaseRecipeInterface provide all method needed in execution of recipe (train, prepare and validate)
Migrate RecipeBowl to use BaseRecipeInterface instead of RecipeInterface
Migrate Chef to use BaseRecipeInterface instead of RecipeInterface
Mapping in Bowl can be map to an array of string, instead a string to map several possibilities in the workplan (stop on first found occurence)

## [2.1.0-beta2] - 2020-10-02
### Beta Release
Change CookbookInterface to be compliant with RecipeInterface

## [2.1.0-beta1] - 2020-10-01
### Beta Release
Add CookbookInterface behavior to provide to developer a new way to define recipe to inject into Chef
Chef support also Cookbook for new recipe or a new sub recipe

[2.0.9] - 2020-09-18
### Stable Release
- Update QA and CI tools
- Update description

## [2.0.8] - 2020-08-25
### Stable Release
### Update
- Update libs and dev libs requirements

## [2.0.7] - 2020-07-17
### Stable Release
### Change
- Add travis run also with lowest dependencies.

## [2.0.6] - 2020-07-14
### Fix
- Fix error when a recipe is validate without dish.

## [2.0.5] - 2020-06-08
### Update
- Require Teknoo States 4.0.9 to support PHPStan 0.12.26

## [2.0.4] - 2020-05-29
### Fix
- Revert Method : Chef::begin() use new static() instead of new self().

## [2.0.3] - 2020-05-28
### Stable Release
### Changes
- Require State 4.0.7
- Replace initializeProxy by initializeStateProxy to avoid collision with other libs

## [2.0.2] - 2020-03-01
### Stable Release
### Changes
- Migrate PHPStan extension from src folder to infrastructures folder (namespace stay unchanged)

## [2.0.1] - 2020-01-29
### Stable Release
- Require Teknoo State 4.0.1+
- Update requirement for dev tools

## [2.0.0] - 2020-01-14
### Stable Release

## [2.0.0-beta7] - 2020-01-07
### Change
- Update to support last PHPStan 0.12.4

## [2.0.0-beta5] - 2019-12-30
### Change
- Update copyright

## [2.0.0-beta4] - 2019-12-23
### Change
- Fix Make definitions tools
- Fix QA issues spotted by PHPStan
- Enable PHPStan extension dedicated to support Stated classes

## [2.0.0-beta3] - 2019-11-28
### Change
- Enable PHPStan in QA Tools

## [2.0.0-beta2] - 2019-11-28
### Change
- Most methods have been updated to include type hints where applicable. Please check your extension points to make sure the function signatures are correct.
_ All files use strict typing. Please make sure to not rely on type coercion.

## [2.0.0-beta1] - 2019-11-27
### Change
- PHP 7.4 is the minimum required
- Switch to typed properties
- Remove some PHP useless DockBlocks
- Replace array_merge by "..." operators for integer indexed arrays

## [1.3.5] - 2019-10-24
### Release
- Maintenance release, QA and update dev vendors requirements

## [1.3.4] - 2019-06-09
### Release
- Maintenance release, upgrade composer dev requirement and libs

## [1.3.3] - 2019-02-10
Stable release
### Update
- Remove support of PHP 7.2
- Swtich to PHPUnit 8.0

## [1.3.2] - 2019-01-04
Stable release
### Update
- QA - Check technical debt
- Add support to PHP 7.3

## [1.3.1] - 2018-12-18
Stable release
### Fix
- When an error handler is defined, exception is not rethrowed automatically, but the recipe remains interrupted.

## [1.3.0] - 2018-12-18
Stable release
### Update
- Recipe method "onError" can now be called several times to add multiple error handler
- Chef can now manage multiple error handler. Interface has been updated to avoid bc break and allow single onError
  and an array of Bowl.

## [1.2.2] - 2018-10-27
Stable release
### Fix
- Fix issue with bowl and optionals arguments not correctly setted when a previous optional argument
(following the order of the declaration) was not set. 

## [1.2.1] - 2018-08-26
Stable release
### Fix
- Fix composer requirement about teknoo/states.

## [1.2.0] - 2018-08-26
Stable release
### Add
- Recipe method "onError" to define a callable or a BowlInterface instance to execute when an exception
is occurred during a recipe's cooking. The exception still be rethrown by the chef after.

## [1.1.2] - 2018-08-16
Stable release
### Add
- Bowl and DynamicBowl can pass to the special parameter "_methodName", the name of the current step.

## [1.1.1] - 2018-08-15
Stable release
### Fix
- Clean and fix behavior tests, Recipe's dish was not correctly tested.
- Fix Recipe bowl, they have an extra looping because the loop counter had a bug.
- Fix recipe compiling when several steps share the same name, firsts was lost.

## [1.1.0] - 2018-07-18
Stable release
### Fix
- Method ChefInterface::setAsideAndBegin() to reserveAndBegin() to follow the culinary vocabulary.

### Change
- Subchef are initialized with workplan of the master

## [1.1.0-beta4] - 2018-06-10
### Fix
- Method Chef::begin() use new static() instead of new self().

## [1.1.0-beta3] - 2018-06-10
### Update
- The work plan injected into the RecipeBowl inherits of the original workplan

## [1.1.0-beta2] - 2018-06-09
### Add
- DynamicBowl class to allow dynamic step in recipe, with the callable is stored into a variable in the work plan
and not directly defined in the recipe. Only the name of the callable as ingredient into the work plan is mandatory.
This bowl can throw an exception if no callable has found in the workplan, but it can fail silently. 

### Update
- Method "cook" of Recipe can accept now BowlInstance as $action value, in addition to callable value
- Update behat tests to support last adds.

## [1.1.0-beta1] - 2018-06-08
### Add
- Feature "execute" on RecipeInterface and Recipe to embed recipes in another recipe. A subrecipe can be
called severa time, in a loop, defined in parameter
- Feature "reserveAndBegin" on ChefInterface and Chef to support embedded recipe.
- Add RecipeBowl to store in the execution plan a recipe as a bowl to be executed by the chef.

### Update
- Update behat tests to support last adds.

## [1.0.2] - 2018-05-08
### Add
- Update Ingredient to support callback feature to normalize a value.

## [1.0.1] - 2018-05-08
### Add
- Update Ingredient to support also scalar type and can normalize ingredient.

## [1.0.0] - 2018-01-01
### Stable release

## [1.0.0-beta2] - 2017-11-18
### Second beta release

### Added
- Behat tests

## [1.0.0-beta1] - 2017-11-12
### First beta release

### Added
- BowlInterface and Bowl to validate and encapsulate callable before
register them into a recipe and map some ingredients available in the
work plan with callable's parameters. (Immutable behavior)
- DishInterface and Dish to validate the result of the execution of a
recipe. (Immutable behavior)
- Ingredient and IngredientInterface to define conditions to execute
a recipe and prepare some ingredient before executing the recipe.
(Immutable behavior)
- Promise (back-ported from teknoo/east-foundation) to define the action
to perform when an operation success or fail when it is not possible
to define exchange between actors via an interface. (Immutable behavior)
- Recipe to define dynamically an ordered set of actions to realize an
algorithm. Actions are callable. (Immutable behavior)
- Chef, can be train by learning a recipe and then cook (execute) the
recipe several times.
