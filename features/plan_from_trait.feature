Feature: Plan from trait
  As a developer, i need to define an ordered and unambiguous operations defined dynamically
  to solve algorithm like a chef cooks a dish.
  This definition is prewritten into a plan, but i can complete the recipe thanks DI container.

  Scenario: Train a chef to cook dish from a plan with the base trait
    Given I have an empty recipe
    And I have a plan with the base trait for date management to get "2017-07-01 10:00:00"
    And I have an untrained chef
    When I train the chef with the plan
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook dish from trait with plan instance overwritting in the worplan
    Given I have an empty recipe
    And I have a plan with the base trait for date management to get "2017-07-01 10:00:00"
    And I add a plan instance to the default workplan
    And I have an untrained chef
    When I train the chef with the plan
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"
    Then the recipe has been successful executed

  Scenario: Train a chef to cook a dish from a plan and custom the recipe with the base trait
    Given I have an empty recipe
    And I have a plan with the base trait for date management
    And I add a step to the recipe to increment the date to get "2017-07-01 16:00:00"
    And I have an untrained chef
    When I train the chef with the plan
    And It starts cooking with "2017-07-01 12:00:00" as "DateTime"
    Then the recipe has been successful executed
