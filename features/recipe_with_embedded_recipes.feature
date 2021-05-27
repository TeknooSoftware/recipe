Feature: Recipe with Embedded recipes
  As a developer, i need to define an ordered and unambiguous operations defined dynamically in sub processes
  to solve algorithm like a chef cooks a dish.
  This definition must be done via a DI container or any solution ables to configure some object following
  a configuration.

  Scenario: Add an ingredient to a recipe
    Given I have an empty recipe
    When I define a "IntBag" to start my recipe
    Then I should have a new recipe.

  Scenario: Add a step to a recipe
    Given I have an empty recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    Then I should have a new recipe.

  Scenario: Add a subrecipe to a recipe
    Given I have an empty recipe
    And I create a subrecipe "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    Then I should have a new recipe.

  Scenario: Add a subrecipe from a cookbook to a recipe
    Given I have an empty recipe
    And I create a subrecipe from cookbook "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    Then I should have a new recipe.

  Scenario: Create a complex recipe with sub recipes
    Given I have an empty recipe
    And I create a subrecipe "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "10"
    Then I should have a new recipe.

  Scenario: Create a complex recipe with error handler with sub recipes
    Given I have an empty recipe
    And I create a subrecipe "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    And I define the behavior on error to do "FeatureContext::onError" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "10"
    Then I should have a new recipe.

  Scenario: Create a complex recipe with error handler with sub recipes with error handler
    Given I have an empty recipe
    And I create a subrecipe "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    And I define the behavior on error to do "FeatureContext::onError" in my sub recipe
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    And I define the behavior on error to do "FeatureContext::onError" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "10"
    Then I should have a new recipe.

  Scenario: Create a complex recipe with sub recipes from a cookbook
    Given I have an empty recipe
    And I create a subrecipe from cookbook "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    And With the step "addValue" to do "IntBag::addValue"
    And And define the default variable "toAdd" in the step "increasingValue" with "5" as "IntBag"
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "13"
    Then I should have a new recipe.

  Scenario: Train a chef to cook a dish with sub recipes
    Given I have an empty recipe
    And I have an untrained chef
    And I create a subrecipe "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "13"
    Then I train the chef with the recipe
    And It starts cooking with "5" as "IntBag"

  Scenario: Train a chef to cook a dish with sub recipes from a cookbook
    Given I have an empty recipe
    And I have an untrained chef
    And I create a subrecipe from cookbook "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    And With the step "addValue" to do "IntBag::addValue"
    And And define the default variable "toAdd" in the step "increasingValue" with "2" as "IntBag"
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "19"
    Then I train the chef with the recipe
    And It starts cooking with "5" as "IntBag"

  Scenario: Train a chef to cook a dish with sub recipes containing an error from a cookbook
    Given I have an empty recipe
    And I have an untrained chef
    And I create a subrecipe from cookbook "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    And With the step "throwError" to do "FeatureContext::createException"
    And And define the default variable "toAdd" in the step "increasingValue" with "2" as "IntBag"
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    When I define the excepted dish "IntBag" to my recipe
    And I must obtain an IntBag with value "19"
    Then I train the chef with the recipe
    And It starts cooking with "5" as "IntBag" and obtain an error

  Scenario: Train a chef to cook a dish  with error handler and with sub recipes containing an error from a cookbook
    Given I have an empty recipe
    And I have an untrained chef
    And I create a subrecipe from cookbook "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    And With the step "throwError" to do "FeatureContext::createException"
    And And define the default variable "toAdd" in the step "increasingValue" with "2" as "IntBag"
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    When I define the behavior on error to do "FeatureContext::onError" my recipe
    Then I train the chef with the recipe
    And It starts cooking with "5" as "IntBag" and obtain an catched error with message "There had an error"

  Scenario: Train a chef to cook a dish with sub recipes containing an error from a cookbook with error handler only on subrecipe
    Given I have an empty recipe
    And I have an untrained chef
    And I create a subrecipe "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    And With the step "throwError" to do "FeatureContext::createException"
    And I define the behavior on error to do "FeatureContext::onError" in my sub recipe
    And And define the default variable "toAdd" in the step "increasingValue" with "2" as "IntBag"
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    Then I train the chef with the recipe
    And It starts cooking with "5" as "IntBag" and obtain an error

  Scenario: Train a chef to cook a dish with sub recipes containing an error from a cookbook with error handler only on subrecipe but error reporting is disabled
    Given I have an empty recipe
    And I have an untrained chef
    And I create a subrecipe "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    And With the step "throwError" to do "FeatureContext::createException"
    And I define the behavior on error to do "FeatureContext::onErrorWithStopRepporing" in my sub recipe
    And And define the default variable "toAdd" in the step "increasingValue" with "2" as "IntBag"
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "1" times
    Then I train the chef with the recipe
    And It starts cooking with "5" as "IntBag" and obtain an catched error with message "There had an error"

  Scenario: Train a chef to cook a dish with sub recipes containing an error from a cookbook with different error handler
    Given I have an empty recipe
    And I have an untrained chef
    And I create a subrecipe "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    And I define the behavior on error to do "FeatureContext::onErrorInSub" in my sub recipe
    When I define the behavior on error to do "FeatureContext::onError" my recipe
    And And define the default variable "toAdd" in the step "increasingValue" with "2" as "IntBag"
    When I define the step "createAnError" to do "FeatureContext::createException" my recipe
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    Then I train the chef with the recipe
    And It starts cooking with "5" as "IntBag" and obtain an catched error with message "There had an error"
