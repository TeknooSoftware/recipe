Feature: Recipe with dynamic bowl
  As a developer, i need to define an ordered and unambiguous operations defined dynamically
  to solve algorithm like a chef cooks a dish. Some operations can only be defined during the cook, they must be dynamic.
  This definition must be done via a DI container or any solution ables to configure some object following
  a configuration.

  Scenario: Add an ingredient to a recipe
    Given I have an empty recipe
    When I define a "\DateTime" to start my recipe
    Then I should have a new recipe.

  Scenario: Add a step to a recipe
    Given I have an empty recipe
    When I define the dynamic step "createImmutable" my recipe
    Then I should have a new recipe.

  Scenario: Set the excepted dish
    Given I have an empty recipe
    When I define the excepted dish "DateTimeImmutable" to my recipe
    Then I should have a new recipe.

  Scenario: Create a complex dynamic recipe
    Given I have an empty recipe
    When I define a "\DateTime" to start my recipe
    And I define a "\Closure" to start my recipe
    When I define the dynamic step "createImmutable" my recipe
    When I set the dynamic callable "createImmutable" to "DateTimeImmutable::createFromMutable" my recipe
    When I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    Then I should have a new recipe.

  Scenario: Train a chef to cook a dynamic dish
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" to start my recipe
    When I define the dynamic step "createImmutable" my recipe
    When I set the dynamic callable "createImmutable" to "DateTimeImmutable::createFromMutable" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"

  Scenario: Train a chef to cook a dynamic dish without non mandatory callable
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" to start my recipe
    When I define the dynamic step "createImmutable" my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an DateTime at "2017-07-01 10:00:00"
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"

  Scenario: Train a chef to cook a dynamic dish with a mandatory step
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" to start my recipe
    When I define the mandatory dynamic step "createImmutable" my recipe
    When I set the dynamic callable "createImmutable" to "DateTimeImmutable::createFromMutable" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"

  Scenario: Train a chef to cook a dynamic dish without callable with a mandatory step
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" to start my recipe
    When I define the mandatory dynamic step "createImmutable" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    Then I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime" and obtain an error
