#Teknoo Software - Recipe - Change Log

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
- Feature "setAsideAndBegin" on ChefInterface and Chef to support embedded recipe.
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
