Feature: Cookbook
  As a developer, i need to define an ordered and unambiguous operations defined dynamically
  to solve algorithm like a chef cooks a dish.
  This definition is prewritten into a cookbook, but i can complete the recipe thanks DI container.

  Scenario: Train a chef to cook a dish from a cookbook
    Given I have an empty recipe
    And I have a cookbook for date management to get "2017-07-01 10:00:00"
    And I have an untrained chef
    Then I train the chef with the cookbook
    And It starts cooking with "2017-07-01 10:00:00" as "DateTime"

  Scenario: Train a chef to cook a dish from a cookbook with a value in mapping
    Given I have an empty recipe
    And I have a cookbook to lowercase value in mapping to get "abcdefghijkl"
    And I have an untrained chef
    Then I train the chef with the cookbook
    And It starts cooking with "AbCdE" as "part1"

  Scenario: Train a chef to cook a dish from a cookbook and custom the recipe
    Given I have an empty recipe
    And I have a cookbook for date management
    And I add a step to the recipe to increment the date to get "2017-07-01 16:00:00"
    And I have an untrained chef
    Then I train the chef with the cookbook
    And It starts cooking with "2017-07-01 12:00:00" as "DateTime"
