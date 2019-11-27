#Teknoo Software - Recipe - Change Log

##[2.0.0-beta1] - 2019-11-27
###Change
- PHP 7.4 is the minimum required
- Switch to typed properties
- Remove some PHP useless DockBlocks
- Replace array_merge by "..." operators for integer indexed arrays

##[1.3.5] - 2019-10-24
###Release
- Maintenance release, QA and update dev vendors requirements

##[1.3.4] - 2019-06-09
###Release
- Maintenance release, upgrade composer dev requirement and libs

##[1.3.3] - 2019-02-10
Stable release
###Update
- Remove support of PHP 7.2
- Swtich to PHPUnit 8.0

##[1.3.2] - 2019-01-04
Stable release
###Update
- QA - Check technical debt
- Add support to PHP 7.3

##[1.3.1] - 2018-12-18
Stable release
###Fix
- When an error handler is defined, exception is not rethrowed automatically, but the recipe remains interrupted.

##[1.3.0] - 2018-12-18
Stable release
###Update
- Recipe method "onError" can now be called several times to add multiple error handler
- Chef can now manage multiple error handler. Interface has been updated to avoid bc break and allow single onError
  and an array of Bowl.

##[1.2.2] - 2018-10-27
Stable release
###Fix
- Fix issue with bowl and optionals arguments not correctly setted when a previous optional argument
(following the order of the declaration) was not set. 

##[1.2.1] - 2018-08-26
Stable release
###Fix
- Fix composer requirement about teknoo/states.

##[1.2.0] - 2018-08-26
Stable release
###Add
- Recipe method "onError" to define a callable or a BowlInterface instance to execute when an exception
is occurred during a recipe's cooking. The exception still be rethrown by the chef after.

##[1.1.2] - 2018-08-16
Stable release
###Add
- Bowl and DynamicBowl can pass to the special parameter "_methodName", the name of the current step.

##[1.1.1] - 2018-08-15
Stable release
###Fix
- Clean and fix behavior tests, Recipe's dish was not correctly tested.
- Fix Recipe bowl, they have an extra looping because the loop counter had a bug.
- Fix recipe compiling when several steps share the same name, firsts was lost.

##[1.1.0] - 2018-07-18
Stable release
###Fix
- Method ChefInterface::setAsideAndBegin() to reserveAndBegin() to follow the culinary vocabulary.

###Change
- Subchef are initialized with workplan of the master

##[1.1.0-beta4] - 2018-06-10
###Fix
- Method Chef::begin() use new static() instead of new self().

##[1.1.0-beta3] - 2018-06-10
###Update
- The work plan injected into the RecipeBowl inherits of the original workplan

##[1.1.0-beta2] - 2018-06-09
###Add
- DynamicBowl class to allow dynamic step in recipe, with the callable is stored into a variable in the work plan
and not directly defined in the recipe. Only the name of the callable as ingredient into the work plan is mandatory.
This bowl can throw an exception if no callable has found in the workplan, but it can fail silently. 

###Update
- Method "cook" of Recipe can accept now BowlInstance as $action value, in addition to callable value
- Update behat tests to support last adds.

##[1.1.0-beta1] - 2018-06-08
###Add
- Feature "execute" on RecipeInterface and Recipe to embed recipes in another recipe. A subrecipe can be
called severa time, in a loop, defined in parameter
- Feature "reserveAndBegin" on ChefInterface and Chef to support embedded recipe.
- Add RecipeBowl to store in the execution plan a recipe as a bowl to be executed by the chef.

###Update
- Update behat tests to support last adds.

##[1.0.2] - 2018-05-08
###Add
- Update Ingredient to support callback feature to normalize a value.

##[1.0.1] - 2018-05-08
###Add
- Update Ingredient to support also scalar type and can normalize ingredient.

##[1.0.0] - 2018-01-01
###Stable release

##[1.0.0-beta2] - 2017-11-18
###Second beta release

###Added
- Behat tests

##[1.0.0-beta1] - 2017-11-12
###First beta release

###Added
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
