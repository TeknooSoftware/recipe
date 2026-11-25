Feature: Promises chained to the dish
  As a developer, i need that an exception thrown by the success callback of a promise without failure callback is
  forwarded to the failure callback of the next promise, instead of escaping from the cooking.

  Scenario: An exception of the success callback of the dish promise is forwarded to the next promise
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "addTest" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe with a promise failing on success and a next promise
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the next promise of the dish has received the error "The dish foo bar is refused"

  Scenario: An exception thrown by the next promise failed by the dish promise escapes from the cooking
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "addTest" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe with a promise failing a throwing next promise
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then I obtain an error whose message contains "The dish foo bar is refused"
