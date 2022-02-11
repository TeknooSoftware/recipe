<?php

declare(strict_types=1);

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\Dish\DishClass;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Tests\Recipe\Transformable;

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

    private ?string $secondVar = null;

    /**
     * @var RecipeInterface[]
     */
    private $recipes = [];

    /**
     * @var BaseRecipeInterface[]
     */
    private $subRecipes = [];

    /**
     * @var string
     */
    private $lastSubRecipeName;

    public $notDefaultCookbook = false;

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
        $this->definedClosure = [
            'DateTimeImmutable::createFromMutable' => static function (
                ChefInterface $chef,
                \DateTime $datetime,
                $_methodName
            ) {
                $immutable = \DateTimeImmutable::createFromMutable($datetime);
                $chef->updateWorkPlan([\DateTimeImmutable::class => $immutable]);
                Assert::assertEquals('createImmutable', $_methodName);
            }
        ];

        static::$message = '';
    }

    private function pushRecipe(RecipeInterface $recipe)
    {
        $this->recipes[] = $recipe;
        $this->lastRecipe = $recipe;
    }

    private function setSubRecipe(string $name, BaseRecipeInterface $recipe)
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
     * @When I define a :className and :secondVar variable to start my recipe
     */
    public function iDefineAAndVariableToStartMyRecipe(string $className, string $secondVar)
    {
        $this->secondVar = $secondVar;

        $this->pushRecipe(
            $this->lastRecipe
                ->require(new Ingredient($className, \trim($className, '\\')))
                ->require(new Ingredient('string', $secondVar))
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
     * @When I define the step in fiber :stepName to do :methodName my recipe
     */
    public function iDefineTheStepInFiberToDoMyRecipe(string $stepName, string $methodName)
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new \Teknoo\Recipe\Bowl\FiberBowl(
                    $this->parseMethod($methodName),
                    [],
                    $stepName,
                ),
                $stepName
            )
        );
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
     * @Then It starts cooking with :value as :name
     */
    public function itStartsCookingWithAs($value, $name)
    {
        $value = match ($name) {
            'TransformableDateTime' => new Transformable(new \DateTime($value)),
            default => $value
        };

        if (null !== $this->secondVar) {
            $this->workPlan[$this->secondVar] = \md5($this->secondVar);
        }

        if (!\class_exists($name)) {
            $this->chef->process(\array_merge($this->workPlan, [lcfirst($name) => $value]));
        } else {
            $this->chef->process(\array_merge($this->workPlan, [\trim($name, '\\') => new $name($value)]));
        }
    }

    /**
     * @Then It starts cooking with :value as :name and obtain an error
     */
    public function itStartsCookingWithAsAndObtainAnError($value, $name)
    {
        try {
            if (null !== $this->secondVar) {
                $this->workPlan[$this->secondVar] = \md5($this->secondVar);
            }

            $this->chef->process(\array_merge($this->workPlan, [\trim($name, '\\') => new $name($value)]));
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
     * @Then It starts cooking and obtain an catched error with message :content
     */
    public function itStartsCookingAndObtainAnCatchedErrorWithMessage($content)
    {
        $this->chef->process($this->workPlan);
        Assert::assertEquals($content, static::$message);
        static::$message = '';
    }

    /**
     * @Then It starts cooking with :value as :name and obtain an catched error with message :content
     */
    public function itStartsCookingWithAsAndObtainAnCatchedErrorWithMessage($value, $name, $content)
    {
        if (null !== $this->secondVar) {
            $this->workPlan[$this->secondVar] = \md5($this->secondVar);
        }

        $this->chef->process(\array_merge($this->workPlan, [\trim($name, '\\') => new $name($value)]));

        Assert::assertEquals($content, static::$message);
        static::$message = '';
    }

    /**
     * @When I must obtain an DateTime at :content
     */
    public function iMustObtainAnDatetimeAt($content)
    {
        $this->callbackPromiseSuccess = function ($value) use ($content) {
            Assert::assertInstanceOf(\DateTime::class, $value);
            Assert::assertEquals(new \DateTime($content), $value);
        };
    }

    /**
     * @Then I must obtain an Immutable DateTime at :content
     */
    public function iMustObtainAnImmutableDatetimeAt($content)
    {
        $this->callbackPromiseSuccess = function ($value) use ($content) {
            Assert::assertInstanceOf(\DateTimeImmutable::class, $value);
            Assert::assertEquals(new \DateTimeImmutable($content), $value);
        };
    }

    /**
     * @Then I must obtain an Mutable DateTime at :content
     */
    public function iMustObtainAnMutableDatetimeAt($content)
    {
        $this->callbackPromiseSuccess = function ($value) use ($content) {
            Assert::assertInstanceOf(\DateTime::class, $value);
            Assert::assertEquals(new \DateTime($content), $value);
        };
    }

    /**
     * @Then I must obtain an Transform object
     */
    public function iMustObtainAnTransformObject()
    {
        $this->callbackPromiseSuccess = function ($value) {
            Assert::assertInstanceOf(Transformable::class, $value);
        };
    }

    /**
     * @Then I must obtain an String with at :name
     */
    public function iMustObtainAnStringWithAt($name)
    {
        $this->callbackPromiseSuccess = function ($value) use ($name) {
            Assert::assertInstanceOf(\StringObject::class, $value);
            Assert::assertEquals($name, (string) $value);
        };
    }

    /**
     * @Given I create a subrecipe :name
     */
    public function iCreateASubrecipe($name)
    {
        $this->setSubRecipe($name, new Recipe());
    }

    /**
     * @Given I create a subrecipe from cookbook :name
     */
    public function iCreateASubCookbook($name)
    {
        $class = new class(new Recipe()) implements CookbookInterface {
            use BaseCookbookTrait;

            private array $steps = [];

            public function __construct(RecipeInterface $recipe)
            {
                $this->fill($recipe);
            }

            public function add($name, callable $callback): self
            {
                $this->steps[$name] = $callback;

                return $this;
            }

            protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
            {
                foreach ($this->steps as $name => $action) {
                    $recipe = $recipe->cook($action, $name);
                }

                return $recipe;
            }
        };

        $this->setSubRecipe($name, $class);
    }

    /**
     * @Given With the step :name to do :method
     */
    public function withTheStepToDo($name, $method)
    {
        if ($this->subRecipes[$this->lastSubRecipeName] instanceof CookbookInterface) {
            $recipe = $this->subRecipes[$this->lastSubRecipeName]->add(
                $name,
                $this->parseMethod($method)
            );
        } else {
            $recipe = $this->subRecipes[$this->lastSubRecipeName]->cook(
                $this->parseMethod($method),
                $name
            );
        }

        $this->setSubRecipe(
            $this->lastSubRecipeName,
            $recipe
        );
    }

    /**
     * @Given And define the default variable :name in the step :step with :value as :class
     */
    public function andDefineTheDefaultVariableInTheStepWithAs($name, $step, $value, $class)
    {
        if ($this->subRecipes[$this->lastSubRecipeName] instanceof CookbookInterface) {
            $this->subRecipes[$step]->addToWorkplan(
                $name,
                new $class($value)
            );
        }
    }

    /**
     * @When I define the behavior on error to do :name my recipe
     */
    public function iDefineTheBehaviorOnErrorToDoMyRecipe($name)
    {
        $this->pushRecipe(
            $this->lastRecipe->onError($this->parseMethod($name))
        );
    }

    /**
     * @When I define the behavior on error to do :name in my sub recipe
     */
    public function iDefineTheBehaviorOnErrorToDoInMySubRecipe($name)
    {
        $this->setSubRecipe(
            $this->lastSubRecipeName,
            $this->subRecipes[$this->lastSubRecipeName]->onError($this->parseMethod($name))
        );
    }

    /**
     * @When I include the recipe :name to :method in my recipe to call :count times
     */
    public function iIncludeTheRecipeToInMyRecipeToCallTimes($name, $method, $count)
    {
        $this->pushRecipe(
            $this->lastRecipe->execute($this->subRecipes[$name], $method, (int) $count)
        );
    }

    /**
     * @When I include the recipe :name to :method in my recipe in fiber to call :count times
     */
    public function iIncludeTheRecipeToInMyRecipeInFiberToCallTimes($name, $method, $count)
    {
        $this->pushRecipe(
            $this->lastRecipe->execute(
                recipe: $this->subRecipes[$name],
                name: $method,
                repeat: (int) $count,
                inFiber: true
            )
        );
    }

    /**
     * @When I must obtain an IntBag with value :content
     */
    public function iMustObtainAnIntbagWithValue(int $content)
    {
        $this->callbackPromiseSuccess = function ($value) use ($content) {
            Assert::assertInstanceOf(IntBag::class, $value);
            Assert::assertEquals(new IntBag($content), $value);
        };
    }

    /**
     * @When I must obtain an error message :content
     */
    public function iMustObtainAnErrorMessage($content)
    {
        $this->callbackPromiseSuccess = function () use ($content) {
            Assert::assertEquals($content, static::$message);
            static::$message = '';
        };
    }

    /**
     * @When I define the dynamic step :name my recipe
     */
    public function iDefineTheDynamicStepMyRecipe($name)
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new \Teknoo\Recipe\Bowl\DynamicBowl($name, false, [], $name),
                $name
            )
        );
    }

    /**
     * @When I define the dynamic fiber step :name my recipe
     */
    public function iDefineTheDynamicFiberStepMyRecipe($name)
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new \Teknoo\Recipe\Bowl\DynamicFiberBowl($name, false, [], $name),
                $name
            )
        );
    }

    /**
     * @When I define the mandatory dynamic step :name my recipe
     */
    public function iDefineTheMandatoryDynamicStepMyRecipe($name)
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new \Teknoo\Recipe\Bowl\DynamicBowl($name, true, [], $name),
                $name
            )
        );
    }

    /**
     * @When I define the mandatory dynamic fiber step :name my recipe
     */
    public function iDefineTheMandatoryDynamicFiberStepMyRecipe($name)
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new \Teknoo\Recipe\Bowl\DynamicFiberBowl($name, true, [], $name),
                $name
            )
        );
    }

    /**
     * @When I set the dynamic callable :name to :method my recipe
     */
    public function iSetTheDynamicCallableToMyRecipe($name, $method)
    {
        $this->workPlan[$name] = $this->parseMethod($method);
    }

    public static function passDateWithTransform(#[Transform] \DateTime $transformableDateTime, ChefInterface $chef)
    {
        Assert::assertInstanceOf(\DateTime::class, $transformableDateTime);

        $chef->updateWorkPlan([\DateTime::class => $transformableDateTime]);
    }

    public static function passDateWithTransformNonNamed(#[Transform(Transformable::class)] \DateTime $transformableDateTime, ChefInterface $chef)
    {
        Assert::assertInstanceOf(\DateTime::class, $transformableDateTime);

        $chef->updateWorkPlan([\DateTime::class => $transformableDateTime]);
    }

    public static function passDateWithoutTransform($transformableDateTime, ChefInterface $chef)
    {
        Assert::assertInstanceOf(Transformable::class, $transformableDateTime);

        $chef->updateWorkPlan([Transformable::class => $transformableDateTime]);
    }

    public static function mergeValue(ChefInterface $chef)
    {
        $chef->merge(IntBag::class, new IntBag(5));
    }

    public static function updatedInWorkPlanAMergeableValue(ChefInterface $chef)
    {
        $chef->updateWorkPlan([IntBag::class => new IntBag(7)]);
    }

    public static function removeDate(ChefInterface $chef)
    {
        $chef->cleanWorkPlan('foo', 'DateTime');
    }

    public static function checkDate(\DateTime $dateTime = null)
    {
        if (null !== $dateTime) {
            throw new \RuntimeException('This ingredient must be deleted');
        }
    }

    public static function createException()
    {
        throw new \RuntimeException('There had an error');
    }

    public static function callError(ChefInterface $chef)
    {
        $chef->error(new \RuntimeException('There had an error'));
    }

    public static function onError(\Throwable $exception, ChefInterface $chef)
    {
        static::$message .= $exception->getMessage();
    }

    public static function onErrorWithStopRepporing(\Throwable $exception, ChefInterface $chef)
    {
        $chef->stopErrorReporting();
        $chef->interruptCooking();
        static::$message .= $exception->getMessage();
    }

    public static function onErrorInSub(\Throwable $exception, ChefInterface $chef)
    {
        static::$message .= 'sub : ' . $exception->getMessage();
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
     * @Given I have a cookbook with the base trait for date management
     */
    public function iHaveACookbookWithTheBaseTraitForDateManagement()
    {
        $this->notDefaultCookbook = false;
        $this->cookbook = new class ($this) implements CookbookInterface
        {
            use BaseCookbookTrait;

            private FeatureContext $context;

            public string $expectedDate = '2017-07-01 10:00:00';

            public function __construct(FeatureContext $context)
            {
                $this->context = $context;
            }

            protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
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

                $that = $this;
                $notDefaultCookbook = $this->context->notDefaultCookbook;
                $recipe = $recipe->cook(
                    function (CookbookInterface $cookbook) use ($that, $notDefaultCookbook) {
                        if (true === $notDefaultCookbook) {
                            Assert::assertNotSame($that, $cookbook);
                        } else {
                            Assert::assertSame($that, $cookbook);
                        }
                    },
                    'cookbook aware',
                    [],
                    5
                );

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

                return $recipe;
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

    /**
     * @Given I add a cookbook instance to the default workplan
     */
    public function iAddACookbookInstanceToTheDefaultWorkplan()
    {
        $this->notDefaultCookbook = true;
        $this->cookbook->addToWorkplan(
            CookbookInterface::class,
            new class implements CookbookInterface {
                public function train(ChefInterface $chef): BaseRecipeInterface {}

                public function prepare(array &$workPlan, ChefInterface $chef): BaseRecipeInterface {}

                public function validate(mixed $value): BaseRecipeInterface {}

                public function fill(RecipeInterface $recipe): CookbookInterface {}
            }
        );
    }

}
