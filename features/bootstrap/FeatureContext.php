<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Dish\DishClass;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\Promise\Promise;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * @var ChefInterface
     */
    private $chef;

    /**
     * @var RecipeInterface
     */
    private $lastRecipe;

    /**
     * @var RecipeInterface[]
     */
    private $recipes = [];

    /**
     * @var RecipeInterface[]
     */
    private $subRecipes = [];

    /**
     * @var string
     */
    private $lastSubRecipeName;

    /**
     * @var CookbookInterface
     */
    private $cookbook;

    /**
     * @var callable
     */
    private $callbackPromiseSuccess;

    /**
     * @var array
     */
    private $workPlan = [];

    /**
     * @var array|callable[]
     */
    private $definedClosure = [];

    /**
     * @var string
     */
    private static $message;

    /**
     * FeatureContext constructor.
     */
    public function __construct()
    {
        $createFromMutable = function (ChefInterface $chef, \DateTime $datetime, $_methodName) {
            $immutable = \DateTimeImmutable::createFromMutable($datetime);
            $chef->updateWorkPlan([\DateTimeImmutable::class => $immutable]);
            Assert::assertEquals('createImmutable', $_methodName);
        };
        $this->definedClosure['DateTimeImmutable::createFromMutable'] = $createFromMutable;
    }

    private function pushRecipe(RecipeInterface $recipe)
    {
        $this->recipes[] = $recipe;
        $this->lastRecipe = $recipe;
    }

    private function setSubRecipe(string $name, RecipeInterface $recipe)
    {
        $this->subRecipes[$name] = $recipe;
        $this->lastSubRecipeName = $name;
    }

    public function parseMethod($method): callable
    {
        if (isset($this->definedClosure[$method])) {
            return $this->definedClosure[$method];
        }

        if (false !== \strpos($method, '::')) {
            return \explode('::', $method);
        }

        return $method;
    }

    /**
     * @Given I have an empty recipe
     */
    public function iHaveAnEmptyRecipe()
    {
        $this->pushRecipe(new Recipe());
    }

    /**
     * @When I define a :className to start my recipe
     */
    public function iDefineAToStartMyRecipe(string $className)
    {
        $this->pushRecipe(
            $this->lastRecipe->require(new Ingredient($className, \trim($className, '\\')))
        );
    }

    /**
     * @When I define the step :stepName to do :methodName my recipe
     */
    public function iDefineTheStepToDoMyRecipe(string $stepName, string $methodName)
    {
        $this->pushRecipe($this->lastRecipe->cook($this->parseMethod($methodName), $stepName));
    }

    /**
     * @When I define the excepted dish :className to my recipe
     */
    public function iDefineTheExceptedDishToMyRecipe(string $className)
    {
        $promise = new Promise(function ($value) {
            ($this->callbackPromiseSuccess)($value);
        }, function () {
            Assert::fail('The dish is not valid');
        });

        $this->pushRecipe($this->lastRecipe->cook(function (ChefInterface $chef, $result) {
            $chef->finish($result);
        }, 'finish', ['result' => \trim($className, '\\')]));

        $this->pushRecipe($this->lastRecipe->given(new DishClass($className, $promise)));
    }

    /**
     * @Then I should have a new recipe.
     */
    public function iShouldHaveANewRecipe()
    {
        Assert::assertNotEmpty($this->recipes);
        $lastRecipe = null;
        foreach ($this->recipes as $recipe) {
            Assert::assertInstanceOf(RecipeInterface::class, $recipe);
            if ($lastRecipe instanceof RecipeInterface) {
                Assert::assertNotSame($lastRecipe, $recipe);
            }
            $lastRecipe = $recipe;
        }
    }

    /**
     * @Given I have an untrained chef
     */
    public function iHaveAnUntrainedChef()
    {
        $this->chef = new Teknoo\Recipe\Chef();
    }

    /**
     * @Then I train the chef with the recipe
     */
    public function iTrainTheChefWithTheRecipe()
    {
        $this->chef->read($this->lastRecipe);
    }

    /**
     * @Then It starts cooking with :arg1 as :arg2
     */
    public function itStartsCookingWithAs($arg1, $arg2)
    {
        $this->chef->process(\array_merge($this->workPlan, [\trim($arg2, '\\') => new $arg2($arg1)]));
    }

    /**
     * @Then It starts cooking with :arg1 as :arg2 and get an error
     */
    public function itStartsCookingWithAsAndGetAnError($arg1, $arg2)
    {
        try {
            $this->chef->process(\array_merge($this->workPlan, [$arg2 => new $arg2($arg1)]));
        } catch (\Throwable $e) {
            return;
        }

        Assert::fail('An error must be thrown');
    }

    /**
     * @Then It starts cooking and obtain an error
     */
    public function itStartsCookingAndObtainAnError()
    {
        try {
            $this->chef->process($this->workPlan);
        } catch (\Throwable $e) {
            static::$message = $e->getMessage();
            ($this->callbackPromiseSuccess)();
            return;
        }

        Assert::fail('An error must be thrown');
    }

    /**
     * @Then It starts cooking and obtain an catched error with message :arg1
     */
    public function itStartsCookingAndObtainAnCatchedErrorWithMessage($arg1)
    {
        $this->chef->process($this->workPlan);
        Assert::assertEquals($arg1, static::$message);
    }

    /**
     * @When I must obtain an DateTime at :arg1
     */
    public function iMustObtainAnDatetimeAt($arg1)
    {
        $this->callbackPromiseSuccess = function ($value) use ($arg1) {
            Assert::assertInstanceOf(\DateTime::class, $value);
            Assert::assertEquals(new \DateTime($arg1), $value);
        };
    }

    /**
     * @Then I must obtain an Immutable DateTime at :arg1
     */
    public function iMustObtainAnImmutableDatetimeAt($arg1)
    {
        $this->callbackPromiseSuccess = function ($value) use ($arg1) {
            Assert::assertInstanceOf(\DateTimeImmutable::class, $value);
            Assert::assertEquals(new \DateTimeImmutable($arg1), $value);
        };
    }

    /**
     * @Then I must obtain an Mutable DateTime at :arg1
     */
    public function iMustObtainAnMutableDatetimeAt($arg1)
    {
        $this->callbackPromiseSuccess = function ($value) use ($arg1) {
            Assert::assertInstanceOf(\DateTime::class, $value);
            Assert::assertEquals(new \DateTime($arg1), $value);
        };
    }

    /**
     * @Then I must obtain an String with at :arg1
     */
    public function iMustObtainAnStringWithAt($arg1)
    {
        $this->callbackPromiseSuccess = function ($value) use ($arg1) {
            Assert::assertInstanceOf(\StringObject::class, $value);
            Assert::assertEquals($arg1, (string) $value);
        };
    }


    /**
     * @Given I create a subrecipe :arg1
     */
    public function iCreateASubrecipe($arg1)
    {
        $this->setSubRecipe($arg1, new \Teknoo\Recipe\Recipe());
    }

    /**
     * @Given With the step :arg1 to do :arg2
     */
    public function withTheStepToDo($arg1, $arg2)
    {
        $this->setSubRecipe(
            $this->lastSubRecipeName,
            $this->subRecipes[$this->lastSubRecipeName]->cook(
                $this->parseMethod($arg2),
                $arg1
            )
        );
    }

    /**
     * @When I define the behavior on error to do :arg1 my recipe
     */
    public function iDefineTheBehaviorOnErrorToDoMyRecipe($arg1)
    {
        $this->pushRecipe(
            $this->lastRecipe->onError($this->parseMethod($arg1))
        );
    }

    /**
     * @When I include the recipe :arg1 to :arg2 in my recipe to call :arg3 times
     */
    public function iIncludeTheRecipeToInMyRecipeToCallTimes($arg1, $arg2, $arg3)
    {
        $this->pushRecipe(
            $this->lastRecipe->execute($this->subRecipes[$arg1], $arg2, (int) $arg3)
        );
    }

    /**
     * @When I must obtain an IntBag with value :arg1
     */
    public function iMustObtainAnIntbagWithValue(int $arg1)
    {
        $this->callbackPromiseSuccess = function ($value) use ($arg1) {
            Assert::assertInstanceOf(IntBag::class, $value);
            Assert::assertEquals(new IntBag($arg1), $value);
        };
    }

    /**
     * @When I must obtain an error message :arg1
     */
    public function iMustObtainAnErrorMessage($arg1)
    {
        $this->callbackPromiseSuccess = function () use ($arg1) {
            Assert::assertEquals($arg1, static::$message);
        };
    }

    /**
     * @When I define the dynamic step :arg1 my recipe
     */
    public function iDefineTheDynamicStepMyRecipe($arg1)
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new \Teknoo\Recipe\Bowl\DynamicBowl($arg1, false, [], $arg1),
                $arg1
            )
        );
    }

    /**
     * @When I define the mandatory dynamic step :arg1 my recipe
     */
    public function iDefineTheMandatoryDynamicStepMyRecipe($arg1)
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new \Teknoo\Recipe\Bowl\DynamicBowl($arg1, true, [], $arg1),
                $arg1
            )
        );
    }

    /**
     * @When I set the dynamic callable :arg1 to :arg2 my recipe
     */
    public function iSetTheDynamicCallableToMyRecipe($arg1, $arg2)
    {
        $this->workPlan[$arg1] = $this->parseMethod($arg2);
    }

    public static function createException()
    {
        throw new \RuntimeException('There had an error');
    }

    public static function onError(\Throwable $exception)
    {
        self::$message = $exception->getMessage();
    }

    /**
     * @Given I have a cookbook for date management
     */
    public function iHaveACookbookForDateManagement()
    {
        $this->cookbook = new class ($this) implements CookbookInterface
        {
            private FeatureContext $context;

            private ?BaseRecipeInterface $recipe;

            public string $expectedDate = '2017-07-01 10:00:00';

            public function __construct(FeatureContext $context)
            {
                $this->context = $context;
            }

            public function train(ChefInterface $chef): BaseRecipeInterface
            {
                $chef->read($this->recipe);

                return $this;
            }

            public function prepare(array &$workPlan, ChefInterface $chef): BaseRecipeInterface
            {
                $this->recipe->prepare($workPlan, $chef);

                return $this;
            }

            public function validate($value): BaseRecipeInterface
            {
                $this->recipe->validate($value);

                return $this;
            }

            public function fill(RecipeInterface $recipe): CookbookInterface
            {
                $recipe = $recipe->require(new Ingredient(\DateTime::class, 'DateTime'));
                $recipe = $recipe->cook(
                    $this->context->parseMethod('DateTimeImmutable::createFromMutable'),
                    'createImmutable',
                    [],
                    1
                );

                $promise = new Promise(function ($value) {
                    Assert::assertInstanceOf(\DateTimeImmutable::class, $value);
                    Assert::assertEquals(new \DateTimeImmutable($this->expectedDate), $value);
                }, function () {
                    Assert::fail('The dish is not valid');
                });

                $recipe = $recipe->cook(
                    function (ChefInterface $chef, $result) {
                        $chef->finish($result);
                    },
                    'finish',
                    ['result' => ['DateTimeImmutable', 'DateTime']],
                    10
                );

                $recipe = $recipe->given(new DishClass(\DateTimeImmutable::class, $promise));

                $this->recipe = $recipe;

                return $this;
            }

        };
    }

    /**
     * @Then I train the chef with the cookbook
     */
    public function iTrainTheChefWithTheCookbook()
    {
        if (null === $this->lastRecipe) {
            $this->pushRecipe(new Recipe());
        }

        $this->cookbook->fill($this->lastRecipe);
        $this->chef->read($this->cookbook);
    }

    /**
     * @Given I add a step to the recipe to increment the date
     */
    public function iAddAStepToTheRecipeToIncrementTheDate()
    {
        $this->pushRecipe(new Recipe());
        $this->pushRecipe(
            $this->lastRecipe->cook(
                function (\DateTime $dateTime, ChefInterface $chef) {
                    $dateTime = $dateTime->modify('+ 2 hours');

                    $chef->updateWorkPlan([
                        'DateTime' => $dateTime
                    ]);
                },
                'IncrementStep',
                [],
                5
            )
        );

        $this->cookbook->expectedDate = '2017-07-01 12:00:00';
    }
}
