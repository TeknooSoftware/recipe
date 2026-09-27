Feature: Chef life cycle between two cookings
  As a developer, i need to reuse a trained chef for several cookings, even if a previous cooking has failed,
  without any leak of ingredients or errors from the previous cooking.

  Scenario: A chef cleans its workplan when an exception escapes from the cooking
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "fail" to do "FeatureContext::failWhenBoomIsPresent" my recipe
    When I define the step "addTest" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "bar bar"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And I add the string "yes" as "boom" in the workplan
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then I obtain an error whose message contains "failed with foo"
    When I remove "boom" from the workplan
    And It starts cooking again with "bar" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: A chef forgets the missing ingredients between two cookings
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "addTest" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo bar"
    When I train the chef with the recipe
    And It starts cooking
    Then I obtain an error whose message contains "missing some ingredients"
    When It starts cooking again with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: A repeated sub recipe loops again on a second cooking
    Given I have an empty recipe
    And I have an untrained chef
    And I create a subrecipe "increment"
    And With the step "increase" to do "Teknoo\Tests\Recipe\Behat\IntBag::increaseValue"
    When I define a "Teknoo\Tests\Recipe\Behat\IntBag" to start my recipe
    When I include the recipe "increment" to "loop" in my recipe while the counter is below 3
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\IntBag" to my recipe for several cookings
    And I must obtain an IntBag with value "13"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "10" as "Teknoo\Tests\Recipe\Behat\IntBag"
    Then the recipe has been successful executed
    When It starts cooking again with "10" as "Teknoo\Tests\Recipe\Behat\IntBag"
    Then the recipe has been successful executed

  Scenario: A chef cloned during a cooking can be reused to cook again
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "addTest" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe
    When I define the step "clone" to do "FeatureContext::cloneTheChef" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe for several cookings
    And I must obtain an String with at "foo bar"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed
    When I use the chef cloned during the cooking
    And It starts cooking again with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed
