Feature: Recipe
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
    When I define the step "createImmutable" to do "DateTimeImmutable::createFromMutable" my recipe
    Then I should have a new recipe.

  Scenario: Set the excepted dish
    Given I have an empty recipe
    When I define the excepted dish "DateTimeImmutable" to my recipe
    Then I should have a new recipe.

  Scenario: Create a complex recipe
    Given I have an empty recipe
    When I define a "\DateTime" to start my recipe
    And I define a "\Closure" to start my recipe
    When I define the step "createImmutable" to do "DateTimeImmutable::createFromMutable" my recipe
    When I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    Then I should have a new recipe.

  Scenario: Train a chef to cook a dish
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" and "temp" variable to start my recipe
    And I define the step "createImmutable" to do "DateTimeImmutable::createFromMutable" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"

  Scenario: Train a chef to cook a dish with two ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" to start my recipe
    And I define the step "createImmutable" to do "DateTimeImmutable::createFromMutable" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook a dish with two ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" to start my recipe
    And I define the step "createImmutable" to do "DateTimeImmutable::createFromMutable" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook a dish with a transformed ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "createImmutable" to do "FeatureContext::passDateWithTransform" my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "TransformableDateTime"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook a dish with a transformed ingredient via a transformer
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "createImmutable" to do "FeatureContext::passDateWithTransformer" my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "string"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook a dish with a transformed non-named ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "createImmutable" to do "FeatureContext::passDateWithTransformNonNamed" my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "TransformableDateTime"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook a dish with a transformed non-named ingredient via a transformer
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "createImmutable" to do "FeatureContext::passDateWithTransformerNonNamed" my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "string"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook a dish with transformable ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "createImmutable" to do "FeatureContext::passDateWithoutTransform" my recipe
    And I define the excepted dish "Teknoo\Tests\Recipe\Transformable" to my recipe
    And I must obtain an Transform object
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "TransformableDateTime"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook a dish with merged ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "increaseValue" to do "FeatureContext::mergeValue" my recipe
    When I define the step "increaseValue" to do "FeatureContext::mergeValue" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\IntBag" to my recipe
    And I must obtain an IntBag with value "15"
    When I train the chef with the recipe
    And It starts cooking with "5" as "Teknoo\Tests\Recipe\Behat\IntBag"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook a dish with mergeable ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "increaseValue" to do "FeatureContext::updatedInWorkPlanAMergeableValue" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\IntBag" to my recipe
    And I must obtain an IntBag with value "7"
    When I train the chef with the recipe
    And It starts cooking with "5" as "Teknoo\Tests\Recipe\Behat\IntBag"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook a dish and remove an ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" to start my recipe
    And I define the step "removeIngredient" to do "FeatureContext::removeDate" my recipe
    And I define the step "checkMissingIngredient" to do "FeatureContext::checkDate" my recipe
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook and have an error without error handler
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "createAnError" to do "FeatureContext::createException" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    When I train the chef with the recipe
    And It starts cooking
    Then I obtain an error

  Scenario: Train a chef to cook and have step send an error
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "callAnError" to do "FeatureContext::callError" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    When I train the chef with the recipe
    And It starts cooking
    Then I obtain an error

  Scenario: Train a chef to cook and have an error with error handler
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "createAnError" to do "FeatureContext::createException" my recipe
    And I define the behavior on error to do "FeatureContext::onError" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    When I train the chef with the recipe
    And It starts cooking
    Then I obtain an catched error with message "There had an error"

