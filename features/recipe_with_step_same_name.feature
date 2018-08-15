Feature: Recipe
  As a developer, i need to define an ordered and unambiguous operations defined dynamically
  to solve algorithm with several repeated actions, like a chef cooks a dish.
  This definition must be done via a DI container or any solution ables to configure some object following
  a configuration.

  Scenario: Add an ingredient to a recipe
    Given I have an empty recipe
    When I define a "\StringObject" to start my recipe
    Then I should have a new recipe.

  Scenario: Add a step to a recipe
    Given I have an empty recipe
    When I define the step "addTest" to do "\StringObject::addTest" my recipe
    Then I should have a new recipe.

  Scenario: Set the excepted dish
    Given I have an empty recipe
    When I define the excepted dish "\StringObject" to my recipe
    And I must obtain an String with at "foo bar bar"
    Then I should have a new recipe.

  Scenario: Create a complex recipe with repeated actions
    Given I have an empty recipe
    When I define a "\StringObject" to start my recipe
    And I define a "\Closure" to start my recipe
    When I define the step "addTest" to do "\StringObject::addTest" my recipe
    When I define the step "addTest" to do "\StringObject::addTest" my recipe
    When I define the excepted dish "\StringObject" to my recipe
    And I must obtain an String with at "foo bar bar"
    Then I should have a new recipe.

  Scenario: Train a chef to cook a dish with repeated actions
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "\StringObject" to start my recipe
    When I define the step "addTest" to do "\StringObject::addTest" my recipe
    When I define the step "addTest" to do "\StringObject::addTest" my recipe
    And I define the excepted dish "\StringObject" to my recipe
    And I must obtain an String with at "foo bar bar"
    Then I train the chef with the recipe
    And It starts cooking with "foo" as "\StringObject"
