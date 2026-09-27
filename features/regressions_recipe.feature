Feature: Recipe steps ordering and parameters mapping
  As a developer, i need to define precisely the order of the steps of my recipe, with explicit positions or
  relative positions, and to map the ingredients of the workplan to the parameters of my steps.

  Scenario: A step at position zero runs first
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "step1" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe at position 10
    When I define the step "step2" to do "Teknoo\Tests\Recipe\Behat\StringObject::addAnotherTest" my recipe at position 20
    When I define the step "first" to do "Teknoo\Tests\Recipe\Behat\StringObject::addAnotherAnotherTest" my recipe at position 0
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo <3 bar foo"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: Insert a step after another step with explicit positions
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "step1" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe at position 10
    When I define the step "step2" to do "Teknoo\Tests\Recipe\Behat\StringObject::addAnotherTest" my recipe at position 20
    When I define the step "step3" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe at position 30
    When I define the step "inserted" to do "Teknoo\Tests\Recipe\Behat\StringObject::addAnotherAnotherTest" my recipe after "step2"
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo bar foo <3 bar"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: Insert a step before another step with explicit positions
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "step1" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe at position 10
    When I define the step "step2" to do "Teknoo\Tests\Recipe\Behat\StringObject::addAnotherTest" my recipe at position 20
    When I define the step "step3" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe at position 30
    When I define the step "inserted" to do "Teknoo\Tests\Recipe\Behat\StringObject::addAnotherAnotherTest" my recipe before "step2"
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo bar <3 foo bar"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: Insert a step relative to an unknown step appends it at the end
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "step1" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe at position 10
    When I define the step "step2" to do "Teknoo\Tests\Recipe\Behat\StringObject::addAnotherTest" my recipe at position 20
    When I define the step "step3" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe at position 30
    When I define the step "inserted" to do "Teknoo\Tests\Recipe\Behat\StringObject::addAnotherAnotherTest" my recipe after "unknown"
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo bar foo bar <3"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: Two steps wrapping internal PHP methods do not share their parameters
    Given I have an empty recipe
    And I have an untrained chef
    And I add an ArrayObject named "ArrayObject" in the workplan
    And I add the internal method "append" of "ArrayObject" as the dynamic callable "append" in the workplan
    And I add the internal method "offsetSet" of "ArrayObject" as the dynamic callable "set" in the workplan
    And I add the string "a" as "value" in the workplan
    And I add the string "k" as "key" in the workplan
    When I define the dynamic step "append" my recipe
    When I define the dynamic step "set" my recipe
    When I define the excepted dish "ArrayObject" to my recipe
    And I must obtain an ArrayObject with the values "a,a"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking
    Then the recipe has been successful executed

  Scenario: A dynamic step follows the callable defined for each cooking
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the dynamic step "dynamic" my recipe
    When I set the dynamic callable "dynamic" to "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe for several cookings
    And I must obtain an String with at "foo bar"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed
    When I set the dynamic callable "dynamic" to "FeatureContext::appendExtra" my recipe
    And I add the string " extra" as "extra" in the workplan
    And I must obtain an String with at "foo extra"
    And It starts cooking again with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: A transformed ingredient is transformed at each cooking
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "createImmutable" to do "FeatureContext::passDateWithTransform" my recipe
    And I define the excepted dish "DateTime" to my recipe for several cookings
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "TransformableDateTime"
    Then the recipe has been successful executed
    When I must obtain an Mutable DateTime at "2018-08-02 11:00:00"
    And It starts cooking again with "2018-08-02 11:00:00" as "TransformableDateTime"
    Then the recipe has been successful executed

  Scenario: A step defined by a static method as a callable string
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "addTest" to do the callable string "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo bar"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: A step with an optional variadic parameter without ingredient
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "addTest" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe
    When I define the step "variadic" to do "FeatureContext::appendVariadic" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo bar"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: A step with a variadic parameter receiving an ingredient
    Given I have an empty recipe
    And I have an untrained chef
    And I add the string " x" as "extras" in the workplan
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "addTest" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe
    When I define the step "variadic" to do "FeatureContext::appendVariadic" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo bar x"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: A mapping can fall back to an ingredient named 0
    Given I have an empty recipe
    And I have an untrained chef
    And I add the string " zero" as "0" in the workplan
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "append" to do "FeatureContext::appendExtra" my recipe with the mapping "extra=missing|0"
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo zero"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  Scenario: A step with a parameter typed with an unknown class reports a missing parameter
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "unknown" to do "FeatureContext::needAnUnknownClass" my recipe
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then I obtain an error whose message contains "Missing the parameter value"

  Scenario: Continue to a step named 0
    Given I have an empty recipe
    And I have an untrained chef
    When I define a "Teknoo\Tests\Recipe\Behat\StringObject" to start my recipe
    When I define the step "addTest1" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe
    When I define the step "goto" to do "FeatureContext::gotToStepZero" my recipe
    When I define the step "skipped" to do "Teknoo\Tests\Recipe\Behat\StringObject::addAnotherTest" my recipe
    When I define the step "0" to do "Teknoo\Tests\Recipe\Behat\StringObject::addTest" my recipe
    When I define the excepted dish "Teknoo\Tests\Recipe\Behat\StringObject" to my recipe
    And I must obtain an String with at "foo bar bar"
    Then I should have a new recipe.
    When I train the chef with the recipe
    And It starts cooking with "foo" as "Teknoo\Tests\Recipe\Behat\StringObject"
    Then the recipe has been successful executed

  @php85
  Scenario: Train a chef to cook a dish with a transformed ingredient via a first-class callable transformer
    Given I have an empty recipe
    And I have an untrained chef
    When I define the step "createImmutable" to do "Teknoo\Tests\Recipe\Php85\FirstClassCallableTransformers::passDateWithFirstClassCallableTransformer" my recipe
    And I define the excepted dish "DateTime" to my recipe
    And I must obtain an Mutable DateTime at "2017-07-01 10:00:00"
    When I train the chef with the recipe
    And It starts cooking with "2017-07-01 10:00:00" as "string"
    Then the recipe has been successful executed
