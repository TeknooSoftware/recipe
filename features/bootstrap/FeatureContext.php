<?php

use Behat\Behat\Context\Context;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;
use PHPUnit\Framework\Assert;
use Teknoo\Recipe\Dish\DishClass;
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

    private function parseMethod($method): callable
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
        $this->pushRecipe(new Teknoo\Recipe\Recipe());
    }

    /**
     * @When I define a :arg1 to start my recipe
     * @param string $arg1
     */
    public function iDefineAToStartMyRecipe(string $arg1)
    {
        $this->pushRecipe(
            $this->lastRecipe->require(new \Teknoo\Recipe\Ingredient\Ingredient($arg1, \trim($arg1, '\\')))
        );
    }

    /**
     * @When I define the step :arg1 to do :arg2 my recipe
     * @param string $arg1
     * @param string $arg2
     */
    public function iDefineTheStepToDoMyRecipe($arg1, $arg2)
    {
        $this->pushRecipe($this->lastRecipe->cook($this->parseMethod($arg2), $arg1));
    }

    /**
     * @When I define the excepted dish :arg1 to my recipe
     * @param string $arg1
     */
    public function iDefineTheExceptedDishToMyRecipe($arg1)
    {
        $promise = new Promise(function ($value) {
            ($this->callbackPromiseSuccess)($value);
        }, function () {
            Assert::fail('The dish is not valid');
        });

        $this->pushRecipe($this->lastRecipe->cook(function (ChefInterface $chef, $result) {
            $chef->finish($result);
        }, 'finish', ['result' => \trim($arg1, '\\')]));

        $this->pushRecipe($this->lastRecipe->given(new DishClass($arg1, $promise)));
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
            $this->chef->process(\array_merge($this->workPlan , [$arg2 => new $arg2($arg1)]));
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
            ($this->callbackPromiseSuccess)();
            return;
        }

        Assert::fail('An error must be thrown');
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
    public function iMustObtainAnIntbagWithValue($arg1)
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
        $this->pushRecipe($this->lastRecipe->cook(
            new \Teknoo\Recipe\Bowl\DynamicBowl($arg1, false, [], $arg1), $arg1)
        );
    }

    /**
     * @When I define the mandatory dynamic step :arg1 my recipe
     */
    public function iDefineTheMandatoryDynamicStepMyRecipe($arg1)
    {
        $this->pushRecipe($this->lastRecipe->cook(
            new \Teknoo\Recipe\Bowl\DynamicBowl($arg1, true, [], $arg1), $arg1)
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
}
