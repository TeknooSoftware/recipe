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
    Then I should have a new recipe.

  Scenario: Train a chef to cook a dish
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\DateTime" to start my recipe
    And I define the step "createImmutable" to do "DateTimeImmutable::createFromMutable" my recipe
    And I define the excepted dish "DateTimeImmutable" to my recipe
    Then I train the chef with the recipe
    And I must obtain an Immutable DateTime at "2017-07-01 10:00:00"
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"
