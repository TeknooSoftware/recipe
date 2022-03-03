Feature: Recipe with fiber
  As a developer, i need to define an ordered and unambiguous operations defined dynamically
  to solve algorithm like a chef cooks a dish.
  This definition must be done via a DI container or any solution ables to configure some object following
  a configuration.

  Scenario: Add an ingredient to a recipe
    Given I have an empty recipe
    When I define a "\DateTime" to start my recipe
    Then I should have a new recipe.

  Scenario: Add a step to a recipe
    Given I have an empty recipe
    When I define the step in fiber "createImmutable" to do "DateTimeImmutable::createFromMutable" my recipe
    Then I should have a new recipe.

  Scenario: Set the excepted dish
    Given I have an empty recipe
    When I define the excepted dish "DateTimeImmutable" to my recipe
    Then I should have a new recipe.

  Scenario: Create a complex recipe
    Given I have an empty recipe
    When I define a "\DateTime" to start my recipe
    And I define a "\Closure" to start my recipe
    When I define the step in fiber "createImmutable" to do "DateTimeImmutable::createFromMutable" my recipe
    When I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    Then I should have a new recipe.

  Scenario: Train a chef to cook a dish
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" and "temp" variable to start my recipe
    And I define the step in fiber "createImmutable" to do "DateTimeImmutable::createFromMutable" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"

  Scenario: Train a chef to cook a dish with two ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" to start my recipe
    And I define the step in fiber "createImmutable" to do "DateTimeImmutable::createFromMutable" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"

  Scenario: Train a chef to cook a dish with two ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" to start my recipe
    And I define the step in fiber "createImmutable" to do "DateTimeImmutable::createFromMutable" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"

  Scenario: Train a chef to cook a dish with a transformed ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "createImmutable" to do "FeatureContext::passDateWithTransform" my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "TransformableDateTime"

  Scenario: Train a chef to cook a dish with a transformed ingredient via a transformer
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "createImmutable" to do "FeatureContext::passDateWithTransformer" my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "string"

  Scenario: Train a chef to cook a dish with a transformed non-named ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "createImmutable" to do "FeatureContext::passDateWithTransformNonNamed" my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "TransformableDateTime"

  Scenario: Train a chef to cook a dish with a transformed non-named ingredient via a transformer
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "createImmutable" to do "FeatureContext::passDateWithTransformerNonNamed" my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "string"

  Scenario: Train a chef to cook a dish with transformable ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "createImmutable" to do "FeatureContext::passDateWithoutTransform" my recipe
    And I define the excepted dish "Teknoo\Tests\Recipe\Transformable" to my recipe
    And I must obtain an Transform object
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "TransformableDateTime"

  Scenario: Train a chef to cook with two dishes in same time
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "fiberStep1" to do "Fiber::step" my recipe
    When I define the step in fiber "fiberStep2" to do "Fiber::step" my recipe
    When I define the step "checkSupervisor" to do "Fiber::checkSupervisorCount" my recipe
    When I define the step "looping" to do "Fiber::looping" my recipe
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "30"
    Then I train the chef with the recipe
    And It starts cooking with "0" as "IntBag"

  Scenario: Train a chef to cook with several dishes in sames times with subrecipes
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "fiberStep1" to do "Fiber::step" my recipe
    And I create a subrecipe "subrecipe"
    And With the step "increaseValue" to do "Fiber::step"
    When I include the recipe "subrecipe" to "increaseInSubRecipe" in my recipe in fiber to call "3" times
    When I define the step "checkSupervisor" to do "Fiber::checkSupervisorCount" my recipe
    When I define the step "looping" to do "Fiber::looping" my recipe
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "60"
    Then I train the chef with the recipe
    And It starts cooking with "0" as "IntBag"

  Scenario: Train a chef to cook with several dishes in sames times with subsubrecipes
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "fiberStep1" to do "Fiber::step" my recipe
    And I create a subrecipe "subsubrecipe"
    And With the step "increaseValue" to do "Fiber::step"
    And I create a subrecipe "subrecipe"
    When I include the recipe "subsubrecipe" to "increaseInSubRecipe" in my subrecipe in fiber to call "2" times
    When I include the recipe "subrecipe" to "increaseInSubRecipe" in my recipe in fiber to call "3" times
    When I define the step "checkSupervisor" to do "Fiber::checkSupervisorCount" my recipe
    When I define the step "looping" to do "Fiber::looping" my recipe
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "105"
    Then I train the chef with the recipe
    And It starts cooking with "0" as "IntBag"

  Scenario: Train a chef to cook a dish with merged ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "increaseValue" to do "FeatureContext::mergeValue" my recipe
    When I define the step in fiber "increaseValue" to do "FeatureContext::mergeValue" my recipe
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "15"
    Then I train the chef with the recipe
    And It starts cooking with "5" as "IntBag"

  Scenario: Train a chef to cook a dish with mergeable ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "increaseValue" to do "FeatureContext::updatedInWorkPlanAMergeableValue" my recipe
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "7"
    Then I train the chef with the recipe
    And It starts cooking with "5" as "IntBag"

  Scenario: Train a chef to cook a dish and remove an ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" to start my recipe
    And I define the step in fiber "removeIngredient" to do "FeatureContext::removeDate" my recipe
    And I define the step in fiber "checkMissingIngredient" to do "FeatureContext::checkDate" my recipe
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"

  Scenario: Train a chef to cook and have an error without error handler
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "createAnError" to do "FeatureContext::createException" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an error message "There had an error"
    Then I train the chef with the recipe
    And It starts cooking and obtain an error

  Scenario: Train a chef to cook and have step send an error
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "callAnError" to do "FeatureContext::callError" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an error message "There had an error"
    Then I train the chef with the recipe
    And It starts cooking and obtain an error

  Scenario: Train a chef to cook and have an error with error handler
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step in fiber "createAnError" to do "FeatureContext::createException" my recipe
    And I define the behavior on error to do "FeatureContext::onError" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    Then I train the chef with the recipe
    And It starts cooking and obtain an catched error with message "There had an error"
