Feature: Ingredients named as a builtin type
  As a developer, i need that a parameter of a builtin type (`string`, `int`, `mixed`...) never takes an ingredient
  named as its type: only a class or an interface is a key of the workplan. Else an ingredient given by an end user,
  for example a parameter of a request, can replace an optional parameter of a step.

  Scenario: An ingredient named "string" does not replace an optional string parameter
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "append" to do "FeatureContext::appendSuffixWithDefault" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo default"
    When I train the chef with the recipe
    And I add the string " injected" as "string" in the workplan
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: An ingredient named "int" does not replace an optional int parameter
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "append" to do "FeatureContext::appendCodeWithDefault" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo 404"
    When I train the chef with the recipe
    And I add the integer "418" as "int" in the workplan
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: An ingredient named as the parameter still gives its value
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "append" to do "FeatureContext::appendSuffixWithDefault" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo by name"
    When I train the chef with the recipe
    And I add the string " injected" as "string" in the workplan
    And I add the string " by name" as "suffix" in the workplan
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed
