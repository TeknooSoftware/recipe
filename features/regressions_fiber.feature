Feature: Fibers supervision and ingredients checking
  As a developer, i need a cooking supervisor resuming my fibers in a predictable order, robust to errors,
  and a reliable checking of the ingredients required by my recipes.

  Scenario: Fibers are resumed in order when a middle fiber ends
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step in fiber "A" to do "FeatureContext::fiberSuspendTwice" my recipe
    When I define the step in fiber "B" to do "FeatureContext::fiberSuspendOnce" my recipe
    When I define the step in fiber "C" to do "FeatureContext::fiberSuspendTwice" my recipe
    When I define the step "loop" to do "Fiber::looping" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo A1 B1 C1 A2 C2"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: Throwing nothing to the supervisor does not fail
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step in fiber "A" to do "FeatureContext::fiberSuspendOnce" my recipe
    When I define the step "throwNothing" to do "FeatureContext::throwNullInSupervisor" my recipe
    When I define the step "loop" to do "Fiber::looping" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo A1"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: Finishing fibers after a missing ingredient in a fiber step terminates
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step in fiber "A" to do "FeatureContext::fiberSuspendOnce" my recipe
    When I define the step in fiber "B" to do "FeatureContext::needAMissingDateTime" my recipe
    When I define the behavior on error to do "FeatureContext::onErrorAndFinishFibers" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo A1"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then I obtain an catched error whose message contains "Missing the parameter missing (missing) in the WorkPlan"
    And the recipe has been successful executed

  Scenario: An unnamed ingredient is found from its type whatever its name in the workplan
    Given I have an empty recipe
    And I have an untrained chef
    When I define an unnamed "DateTime" ingredient to start my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as a "DateTime" instance named "other"
    Then the recipe has been successful executed

  Scenario: An ingredient whose type looks like a PHP function with several arguments is accepted
    Given I have an empty recipe
    And I have an untrained chef
    And I add the string " extra" as "a" in the workplan
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    And I define a "a" to start my recipe
    When I define the step "append" to do "FeatureContext::appendExtra" my recipe with the mapping "extra=a"
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo extra"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: An optional ingredient may be absent from the workplan
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    And I define an optional "string" ingredient named "nickname" to start my recipe
    When I define the step "nickname" to do "FeatureContext::appendNickname" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: An interface ingredient accepts an implementation
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "DateTimeInterface" to start my recipe
    And I define the excepted dish "DateTimeInterface" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as a "DateTime" instance named "DateTimeInterface"
    Then the recipe has been successful executed

  Scenario: An interface ingredient rejects another object
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "DateTimeInterface" to start my recipe
    And I define the excepted dish "DateTimeInterface" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as a "Teknoo\Tests\Recipe\Behat\StringObject" instance named "DateTimeInterface"
    Then I obtain an error whose message contains "must implement DateTimeInterface"

  Scenario: A class ingredient rejects a scalar value
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "DateTime" to start my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with the integer 123 as "DateTime"
    Then I obtain an error whose message contains "must implement DateTime"
