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

  Scenario: Set the excepted dish
    Given I have an empty recipe
    When I define the excepted dish "IntBag" with value "123" to my recipe
    Then I should have a new recipe.

  Scenario: Create a complex recipe with sub recipes
    Given I have an empty recipe
    And I create a subrecipe "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    When I define the excepted dish "IntBag" with value "10" to my recipe
    Then I should have a new recipe.

  Scenario: Train a chef to cook a dish
    Given I have an empty recipe
    And I have an untrained chef
    And I create a subrecipe "increasingValue"
    And With the step "increaseValue" to do "IntBag::increaseValue"
    When I define a "IntBag" to start my recipe
    When I define the step "initializeBag" to do "IntBag::initializeTo10" my recipe
    When I include the recipe "increasingValue" to "increaseValue" in my recipe to call "3" times
    When I define the excepted dish "IntBag" with value "10" to my recipe
    Then I train the chef with the recipe
    And I must obtain an IntBag at "13"
    And It starts cooking with "5" as "IntBag"
