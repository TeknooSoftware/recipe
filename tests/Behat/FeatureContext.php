<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Behat;

use ReflectionObject;
use RuntimeException;
use Behat\Behat\Context\Context;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Fiber;
use PHPUnit\Framework\Assert;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\Bowl\DynamicFiberBowl;
use Teknoo\Recipe\Bowl\FiberBowl;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Cookbook\BaseCookbookTrait;
use Teknoo\Recipe\CookbookInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\Dish\DishClass;
use Teknoo\Recipe\EditablePlanInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Plan\BasePlanTrait;
use Teknoo\Recipe\Plan\EditablePlanTrait;
use Teknoo\Recipe\Plan\Step;
use Teknoo\Recipe\PlanInterface;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Recipe;
use Teknoo\Recipe\RecipeInterface;
use Teknoo\Recipe\Value;
use Teknoo\Tests\Recipe\Transformable;
use Throwable;

use function array_merge;
use function class_exists;
use function explode;
use function lcfirst;
use function strpos;
use function trim;
use function strrev;

/**
 * Defines application features from the specific context.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class FeatureContext implements Context
{
    private ?ChefInterface $chef = null;

    private ?RecipeInterface $lastRecipe = null;

    private ?string $secondVar = null;

    /**
     * @var RecipeInterface[]
     */
    private array $recipes = [];

    /**
     * @var BaseRecipeInterface[]
     */
    private array $subRecipes = [];

    private string $lastSubRecipeName = '';

    public bool $notDefaultPlan = false;

    private ?PlanInterface $plan = null;

    /**
     * @var callable
     */
    private $callbackPromiseSuccess;

    private array $workPlan = [];

    /**
     * @var array|callable[]
     */
    private array $definedClosure = [];

    private static string $message;

    /**
     * FeatureContext constructor.
     */
    public function __construct()
    {
        $this->definedClosure = [
            'DateTimeImmutable::createFromMutable' => static function (
                ChefInterface $chef,
                DateTime $datetime,
                $_methodName
            ): void {
                $immutable = DateTimeImmutable::createFromMutable($datetime);
                $chef->updateWorkPlan([DateTimeImmutable::class => $immutable]);
                Assert::assertEquals('createImmutable', $_methodName);
            },
            'Fiber::step' => static function (IntBag $bag): void {
                for ($i = 0; $i < 15; ++$i) {
                    Fiber::suspend();
                    IntBag::increaseValue($bag);
                }
            },
            'Fiber::checkSupervisorCount' => static function (CookingSupervisorInterface $supervisor): void {
                //Tests to check integrity of the cooking supervisor in a complex situation

                $ro = new ReflectionObject($supervisor);
                $rp = $ro->getProperty('items');
                $rp->setAccessible(true);
                $iterator = $rp->getValue($supervisor);
                $rp->setAccessible(false);

                if (2 === $iterator->count()) {
                    foreach ($iterator as $item) {
                        Assert::assertInstanceOf(Fiber::class, $item);
                    }
                } else {
                    Assert::assertInstanceOf(Fiber::class, $iterator->current());
                    $iterator->next();
                    Assert::assertInstanceOf(CookingSupervisorInterface::class, $iterator->current());
                    $iterator->next();
                    Assert::assertInstanceOf(Fiber::class, $iterator->current());
                    $iterator->next();
                    Assert::assertInstanceOf(CookingSupervisorInterface::class, $iterator->current());
                    $iterator->next();
                    Assert::assertInstanceOf(Fiber::class, $iterator->current());
                    $iterator->next();
                    Assert::assertInstanceOf(CookingSupervisorInterface::class, $iterator->current());
                    $iterator->next();
                    Assert::assertInstanceOf(Fiber::class, $iterator->current());
                }

                $iterator->rewind();
            },
            'Fiber::looping' => static function (CookingSupervisorInterface $supervisor): void {
                $supervisor->finish();
            },
        ];

        static::$message = '';
    }

    private function pushRecipe(RecipeInterface $recipe): void
    {
        $this->recipes[] = $recipe;
        $this->lastRecipe = $recipe;
    }

    private function setSubRecipe(string $name, BaseRecipeInterface $recipe): void
    {
        $this->subRecipes[$name] = $recipe;
        $this->lastSubRecipeName = $name;
    }

    public function parseMethod($method): callable
    {
        if (isset($this->definedClosure[$method])) {
            return $this->definedClosure[$method];
        }

        if (str_contains((string) $method, '::')) {
            $callable = explode('::', (string) $method);

            if ('FeatureContext' === $callable[0]) {
                return self::{$callable[1]}(...);
            }

            return $callable;
        }

        return $method;
    }

    /**
     * @Given I have an empty recipe
     */
    public function iHaveAnEmptyRecipe(): void
    {
        $this->pushRecipe(new Recipe());
    }

    /**
     * @When I define a :className to start my recipe
     */
    public function iDefineAToStartMyRecipe(string $className): void
    {
        $this->pushRecipe(
            $this->lastRecipe->require(new Ingredient($className, trim($className, '\\')))
        );
    }

    /**
     * @When I define a :className and :secondVar variable to start my recipe
     */
    public function iDefineAAndVariableToStartMyRecipe(string $className, string $secondVar): void
    {
        $this->secondVar = $secondVar;

        $this->pushRecipe(
            $this->lastRecipe
                ->require(new Ingredient($className, trim($className, '\\')))
                ->require(new Ingredient('string', $secondVar))
        );
    }


    /**
     * @When I define the step :stepName to do :methodName my recipe
     */
    public function iDefineTheStepToDoMyRecipe(string $stepName, string $methodName): void
    {
        $this->pushRecipe($this->lastRecipe->cook($this->parseMethod($methodName), $stepName));
    }


    /**
     * @When I define the step in fiber :stepName to do :methodName my recipe
     */
    public function iDefineTheStepInFiberToDoMyRecipe(string $stepName, string $methodName): void
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new FiberBowl(
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
    public function iDefineTheExceptedDishToMyRecipe(string $className): void
    {
        $promise = new Promise(
            function ($value): void {
                ($this->callbackPromiseSuccess)($value);
            }, function (): void {
                Assert::fail('The dish is not valid');
            },
        );

        $this->pushRecipe(
            $this->lastRecipe->cook(
                function (ChefInterface $chef, $result): void {
                    $chef->finish($result);
                },
                'finish',
                ['result' => trim($className, '\\')]
            )
        );

        $this->pushRecipe($this->lastRecipe->given(new DishClass($className, $promise)));
    }

    /**
     * @Then I should have a new recipe.
     */
    public function iShouldHaveANewRecipe(): void
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
    public function iHaveAnUntrainedChef(): void
    {
        $this->chef = new Chef();
    }

    /**
     * @Then I train the chef with the recipe
     */
    public function iTrainTheChefWithTheRecipe(): void
    {
        $this->chef->read($this->lastRecipe);
    }

    /**
     * @Then It starts cooking with :value as :name
     */
    public function itStartsCookingWithAs($value, $name): void
    {
        $value = match ($name) {
            'TransformableDateTime' => new Transformable(new DateTime($value)),
            'string' => $value,
            default => $value
        };

        if ('string' === $name) {
            $name = 'TransformableDateTime';
        }

        if (null !== $this->secondVar) {
            $this->workPlan[$this->secondVar] = \hash('sha256', $this->secondVar);
        }

        if (!class_exists($name)) {
            $this->chef->process(array_merge($this->workPlan, [lcfirst((string) $name) => $value]));
        } else {
            $this->chef->process(array_merge($this->workPlan, [trim($name, '\\') => new $name($value)]));
        }
    }

    /**
     * @Then It starts cooking with :value as :name and obtain an error
     */
    public function itStartsCookingWithAsAndObtainAnError($value, $name): void
    {
        try {
            if (null !== $this->secondVar) {
                $this->workPlan[$this->secondVar] = \hash('sha256', $this->secondVar);
            }

            $this->chef->process(array_merge($this->workPlan, [trim((string) $name, '\\') => new $name($value)]));
        } catch (Throwable) {
            return;
        }

        Assert::fail('An error must be thrown');
    }

    /**
     * @Then It starts cooking and obtain an error
     */
    public function itStartsCookingAndObtainAnError(): void
    {
        try {
            $this->chef->process($this->workPlan);
        } catch (Throwable $e) {
            static::$message = $e->getMessage();
            ($this->callbackPromiseSuccess)();
            return;
        }

        Assert::fail('An error must be thrown');
    }

    /**
     * @Then It starts cooking and obtain an catched error with message :content
     */
    public function itStartsCookingAndObtainAnCatchedErrorWithMessage($content): void
    {
        $this->chef->process($this->workPlan);
        Assert::assertEquals($content, static::$message);
        static::$message = '';
    }

    /**
     * @Then It starts cooking with :value as :name and obtain an catched error with message :content
     */
    public function itStartsCookingWithAsAndObtainAnCatchedErrorWithMessage($value, $name, $content): void
    {
        if (null !== $this->secondVar) {
            $this->workPlan[$this->secondVar] = \hash('sha256', $this->secondVar);
        }

        $this->chef->process(array_merge($this->workPlan, [trim((string) $name, '\\') => new $name($value)]));

        Assert::assertEquals($content, static::$message);
        static::$message = '';
    }

    /**
     * @When I must obtain an DateTime at :content
     */
    public function iMustObtainAnDatetimeAt($content): void
    {
        $this->callbackPromiseSuccess = function ($value) use ($content): void {
            Assert::assertInstanceOf(DateTime::class, $value);
            Assert::assertEquals(new DateTime($content), $value);
        };
    }

    /**
     * @Then I must obtain an Immutable DateTime at :content
     */
    public function iMustObtainAnImmutableDatetimeAt($content): void
    {
        $this->callbackPromiseSuccess = function ($value) use ($content): void {
            Assert::assertInstanceOf(DateTimeImmutable::class, $value);
            Assert::assertEquals(new DateTimeImmutable($content), $value);
        };
    }

    /**
     * @Then I must obtain an Mutable DateTime at :content
     */
    public function iMustObtainAnMutableDatetimeAt($content): void
    {
        $this->callbackPromiseSuccess = function ($value) use ($content): void {
            Assert::assertInstanceOf(DateTime::class, $value);
            Assert::assertEquals(new DateTime($content), $value);
        };
    }

    /**
     * @Then I must obtain an Transform object
     */
    public function iMustObtainAnTransformObject(): void
    {
        $this->callbackPromiseSuccess = function ($value): void {
            Assert::assertInstanceOf(Transformable::class, $value);
        };
    }

    /**
     * @Then I must obtain an String with at :name
     */
    public function iMustObtainAnStringWithAt($name): void
    {
        $this->callbackPromiseSuccess = function ($value) use ($name): void {
            Assert::assertInstanceOf(StringObject::class, $value);
            Assert::assertEquals($name, (string) $value);
        };
    }

    /**
     * @Given I create a subrecipe :name
     */
    public function iCreateASubrecipe(string $name): void
    {
        $this->setSubRecipe($name, new Recipe());
    }

    /**
     * @Given I create a subrecipe from cookbook :name
     */
    public function iCreateASubCookbook(string $name): void
    {
        $class = new class (new Recipe()) implements CookbookInterface {
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
     * @Given I create a subrecipe from plann :name
     */
    public function iCreateASubPlan(string $name): void
    {
        $class = new class (new Recipe()) implements PlanInterface {
            use BasePlanTrait;

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
    public function withTheStepToDo($name, $method): void
    {
        if ($this->subRecipes[$this->lastSubRecipeName] instanceof PlanInterface) {
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
     * @Given With the step in fiber :name to do :method
     */
    public function withTheStepInFiberToDo($name, $method): void
    {
        $bowl = new FiberBowl(
            $this->parseMethod($method),
            [],
            $name
        );

        if ($this->subRecipes[$this->lastSubRecipeName] instanceof PlanInterface) {
            $recipe = $this->subRecipes[$this->lastSubRecipeName]->add(
                $name,
                $bowl,
            );
        } else {
            $recipe = $this->subRecipes[$this->lastSubRecipeName]->cook(
                $bowl,
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
    public function andDefineTheDefaultVariableInTheStepWithAs($name, $step, $value, $class): void
    {
        if ($this->subRecipes[$this->lastSubRecipeName] instanceof PlanInterface) {
            $this->subRecipes[$step]->addToWorkplan(
                $name,
                new $class($value)
            );
        }
    }

    /**
     * @When I define the behavior on error to do :name my recipe
     */
    public function iDefineTheBehaviorOnErrorToDoMyRecipe($name): void
    {
        $this->pushRecipe(
            $this->lastRecipe->onError($this->parseMethod($name))
        );
    }

    /**
     * @When I define the behavior on error to do :name in my sub recipe
     */
    public function iDefineTheBehaviorOnErrorToDoInMySubRecipe($name): void
    {
        $this->setSubRecipe(
            $this->lastSubRecipeName,
            $this->subRecipes[$this->lastSubRecipeName]->onError($this->parseMethod($name))
        );
    }

    /**
     * @When I include the recipe :name to :method in my recipe to call :count times
     */
    public function iIncludeTheRecipeToInMyRecipeToCallTimes($name, $method, $count): void
    {
        $this->pushRecipe(
            $this->lastRecipe->execute($this->subRecipes[$name], $method, (int) $count)
        );
    }

    /**
     * @When I include the recipe :name to :method in my recipe in fiber to call :count times
     */
    public function iIncludeTheRecipeToInMyRecipeInFiberToCallTimes($name, $method, $count): void
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
     * @When I include the recipe :name to :method in my subrecipe in fiber to call :count times
     */
    public function iIncludeTheRecipeToInMySubRecipeInFiberToCallTimes($name, $method, $count): void
    {
        $recipe = $this->subRecipes[$this->lastSubRecipeName]->execute(
            recipe: $this->subRecipes[$name],
            name: $method,
            repeat: (int) $count,
            inFiber: true
        );

        $this->setSubRecipe(
            $this->lastSubRecipeName,
            $recipe
        );
    }

    /**
     * @When I must obtain an IntBag with value :content
     */
    public function iMustObtainAnIntbagWithValue(int $content): void
    {
        $this->callbackPromiseSuccess = function ($value) use ($content): void {
            Assert::assertInstanceOf(IntBag::class, $value);
            Assert::assertEquals(new IntBag($content), $value);
        };
    }

    /**
     * @When I must obtain an error message :content
     */
    public function iMustObtainAnErrorMessage($content): void
    {
        $this->callbackPromiseSuccess = function () use ($content): void {
            Assert::assertEquals($content, static::$message);
            static::$message = '';
        };
    }

    /**
     * @When I define the dynamic step :name my recipe
     */
    public function iDefineTheDynamicStepMyRecipe($name): void
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new DynamicBowl($name, false, [], $name),
                $name
            )
        );
    }

    /**
     * @When I define the dynamic fiber step :name my recipe
     */
    public function iDefineTheDynamicFiberStepMyRecipe($name): void
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new DynamicFiberBowl($name, false, [], $name),
                $name
            )
        );
    }

    /**
     * @When I define the mandatory dynamic step :name my recipe
     */
    public function iDefineTheMandatoryDynamicStepMyRecipe($name): void
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new DynamicBowl($name, true, [], $name),
                $name
            )
        );
    }

    /**
     * @When I define the mandatory dynamic fiber step :name my recipe
     */
    public function iDefineTheMandatoryDynamicFiberStepMyRecipe($name): void
    {
        $this->pushRecipe(
            $this->lastRecipe->cook(
                new DynamicFiberBowl($name, true, [], $name),
                $name
            )
        );
    }

    /**
     * @When I set the dynamic callable :name to :method my recipe
     */
    public function iSetTheDynamicCallableToMyRecipe($name, $method): void
    {
        $this->workPlan[$name] = $this->parseMethod($method);
    }

    public static function passDateWithTransform(
        #[Transform] DateTime $transformableDateTime,
        ChefInterface $chef
    ): void {
        Assert::assertInstanceOf(DateTime::class, $transformableDateTime);

        $chef->updateWorkPlan([DateTime::class => $transformableDateTime]);
    }

    public static function passDateWithTransformNonNamed(
        #[Transform(Transformable::class)] DateTime $transformableDateTime,
        ChefInterface $chef
    ): void {
        Assert::assertInstanceOf(DateTime::class, $transformableDateTime);

        $chef->updateWorkPlan([DateTime::class => $transformableDateTime]);
    }

    public static function passDateWithTransformer(
        #[Transform(transformer: [Transformable::class, 'toTransformable'])] DateTime $transformableDateTime,
        ChefInterface $chef
    ): void {
        Assert::assertInstanceOf(DateTime::class, $transformableDateTime);

        $chef->updateWorkPlan([DateTime::class => $transformableDateTime]);
    }

    public static function passDateWithTransformerNonNamed(
        #[Transform(Transformable::class, [Transformable::class, 'toTransformable'])] DateTime $transformableDateTime,
        ChefInterface $chef
    ): void {
        Assert::assertInstanceOf(DateTime::class, $transformableDateTime);

        $chef->updateWorkPlan([DateTime::class => $transformableDateTime]);
    }

    public static function passDateWithoutTransform($transformableDateTime, ChefInterface $chef): void
    {
        Assert::assertInstanceOf(Transformable::class, $transformableDateTime);

        $chef->updateWorkPlan([Transformable::class => $transformableDateTime]);
    }

    public static function mergeValue(ChefInterface $chef): void
    {
        $chef->merge(IntBag::class, new IntBag(5));
    }

    public static function updatedInWorkPlanAMergeableValue(ChefInterface $chef): void
    {
        $chef->updateWorkPlan([IntBag::class => new IntBag(7)]);
    }

    public static function removeDate(ChefInterface $chef): void
    {
        $chef->cleanWorkPlan('foo', DateTime::Class);
    }

    public static function checkDate(DateTime $dateTime = null): void
    {
        if ($dateTime instanceof DateTime) {
            throw new RuntimeException('This ingredient must be deleted');
        }
    }

    public static function createException(): never
    {
        throw new RuntimeException('There had an error');
    }

    public static function callError(ChefInterface $chef): void
    {
        $chef->error(new RuntimeException('There had an error'));
    }

    public static function onError(Throwable $exception, ChefInterface $chef): void
    {
        static::$message .= $exception->getMessage();
    }

    public static function onErrorWithStopRepporing(Throwable $exception, ChefInterface $chef): void
    {
        $chef->stopErrorReporting();
        $chef->interruptCooking();
        static::$message .= $exception->getMessage();
    }

    /**
     * @Given I have a cookbook for date management to get :expectedDate
     * @Given I have a cookbook for date management
     */
    public function iHaveACookbookForDateManagement(?string $expectedDate = ''): void
    {
        $this->plan = new class ($this, $expectedDate) implements CookbookInterface {
            private ?BaseRecipeInterface $recipe = null;

            public function __construct(
                private readonly FeatureContext $context,
                public string $expectedDate = '2017-07-01 10:00:00',
            ) {
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
                $recipe = $recipe->require(new Ingredient(DateTime::class, DateTime::Class));
                $recipe = $recipe->cook(
                    $this->context->parseMethod('DateTimeImmutable::createFromMutable'),
                    'createImmutable',
                    [],
                    1
                );

                $recipe = $recipe->cook(
                    function (ChefInterface $chef, $result): void {
                        $chef->finish($result);
                    },
                    'finish',
                    ['result' => [DateTimeImmutable::Class, DateTime::Class]],
                    10
                );

                $promise = new Promise(function ($value): void {
                    Assert::assertInstanceOf(DateTimeImmutable::class, $value);
                    Assert::assertEquals(new DateTimeImmutable($this->expectedDate), $value);
                }, function (): never {
                    Assert::fail('The dish is not valid');
                });

                $recipe = $recipe->given(new DishClass(DateTimeImmutable::class, $promise));

                $this->recipe = $recipe;

                return $this;
            }
        };
    }

    public static function onErrorInSub(Throwable $exception, ChefInterface $chef): void
    {
        static::$message .= 'sub : ' . $exception->getMessage();
    }

    /**
     * @Given I have a plan for date management to get :expectedDate
     * @Given I have a plan for date management
     */
    public function iHaveAPlanForDateManagement(?string $expectedDate = ''): void
    {
        $this->plan = new class ($this, $expectedDate) implements PlanInterface {

            private ?BaseRecipeInterface $recipe = null;

            public function __construct(
                private readonly FeatureContext $context,
                public string $expectedDate = '2017-07-01 10:00:00',
            ) {
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

            public function fill(RecipeInterface $recipe): PlanInterface
            {
                $recipe = $recipe->require(new Ingredient(DateTime::class, DateTime::Class));
                $recipe = $recipe->cook(
                    $this->context->parseMethod('DateTimeImmutable::createFromMutable'),
                    'createImmutable',
                    [],
                    1
                );

                $recipe = $recipe->cook(
                    function (ChefInterface $chef, $result): void {
                        $chef->finish($result);
                    },
                    'finish',
                    ['result' => [DateTimeImmutable::Class, DateTime::Class]],
                    10
                );

                $promise = new Promise(function ($value): void {
                    Assert::assertInstanceOf(DateTimeImmutable::class, $value);
                    Assert::assertEquals(new DateTimeImmutable($this->expectedDate), $value);
                }, function (): never {
                    Assert::fail('The dish is not valid');
                });

                $recipe = $recipe->given(new DishClass(DateTimeImmutable::class, $promise));

                $this->recipe = $recipe;

                return $this;
            }
        };
    }

    /**
     * @Given I have an editable plan for date management to get :expectedDate
     * @Given I have an editable plan for date management
     */
    public function iHaveAEditablePlanForDateManagement(?string $expectedDate = ''): void
    {
        $this->notDefaultPlan = false;
        $this->plan = new class ($this, $expectedDate) implements EditablePlanInterface {
            use EditablePlanTrait;

            public function __construct(
                private FeatureContext $context,
                public string $expectedDate = '2017-07-01 10:00:00',
            ) {
            }

            protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
            {
                $recipe = $recipe->require(new Ingredient(DateTime::class, DateTime::Class));
                $recipe = $recipe->cook(
                    $this->context->parseMethod('DateTimeImmutable::createFromMutable'),
                    'createImmutable',
                    [],
                    4
                );

                $promise = new Promise(function ($value): void {
                    Assert::assertInstanceOf(DateTimeImmutable::class, $value);
                    Assert::assertEquals(new DateTimeImmutable($this->expectedDate), $value);
                }, function (): never {
                    Assert::fail('The dish is not valid');
                });

                $that = $this;
                $notDefaultPlan = $this->context->notDefaultPlan;
                $recipe = $recipe->cook(
                    function (PlanInterface $plan) use ($that, $notDefaultPlan): void {
                        if (true === $notDefaultPlan) {
                            Assert::assertNotSame($that, $plan);
                        } else {
                            Assert::assertSame($that, $plan);
                        }
                    },
                    'plan aware',
                    [],
                    5
                );

                $recipe = $recipe->cook(
                    function (ChefInterface $chef, $result): void {
                        $chef->finish($result);
                    },
                    'finish',
                    ['result' => [DateTimeImmutable::Class, DateTime::Class]],
                    10
                );

                $recipe = $recipe->given(new DishClass(DateTimeImmutable::class, $promise));

                $this->recipe = $recipe;

                return $recipe;
            }
        };
    }

    /**
     * @Given I have more steps to edit the date in the plan
     */
    public function iHaveMoreStepsToEditTheDateInThePlan(): void
    {
        Assert::assertInstanceOf(EditablePlanInterface::class, $this->plan);

        $this->plan->add(
            function (ChefInterface $chef, DateTimeInterface $dateTime): void {
                $chef->updateWorkPlan([DateTime::Class => $dateTime->modify('+1 day')]);
            },
            1,
        );

        $this->plan->add(
            new Step(
                step: function (ChefInterface $chef, DateTimeInterface $dateTime): void {
                    $chef->updateWorkPlan([DateTime::Class => $dateTime->modify('-1 year')]);
                }
            ),
            2,
        );
    }

    /**
     * @Given I have more steps to edit the value in the plan
     */
    public function iHaveMoreStepsToEditTheValueInThePlan(): void
    {
        Assert::assertInstanceOf(EditablePlanInterface::class, $this->plan);

        $this->plan->add(
            function (ChefInterface $chef, string $string): void {
                $chef->updateWorkPlan(['string' => strrev($string)]);
            },
            3,
        );
    }

    /**
     * @Given I have a cookbook to lowercase value in mapping to get :expectedResult
     */
    public function iHaveACookbookToLowerCaseValueInMapping(string $expectedResult): void
    {
        $this->plan = new class ($expectedResult) implements CookbookInterface {
            private ?BaseRecipeInterface $recipe = null;

            public function __construct(
                public string $expectedResult = 'abcdef'
            ) {
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
                $recipe = $recipe->require(new Ingredient('string', 'part1'));
                $recipe = $recipe->cook(
                    function (ChefInterface $chef, string $part1, string $part2): void  {
                        $chef->updateWorkPlan(['string' => $part1 . $part2]);
                    },
                    'concatenate',
                    [
                        'part2' => new Value('FgHIjKl'),
                    ],
                    1
                );

                $recipe = $recipe->cook(
                    fn (ChefInterface $chef, string $string): ChefInterface => $chef->updateWorkPlan(
                        ['string' => strtolower($string)]
                    ),
                    'finish',
                    [],
                    2
                );

                $recipe = $recipe->cook(
                    fn (string $string, ChefInterface $chef): ChefInterface => $chef->finish(new StringObject($string)),
                    'createObject',
                    [],
                    2
                );

                $promise = new Promise(function ($value): void {
                    Assert::assertInstanceOf(StringObject::class, $value);
                    Assert::assertEquals(new StringObject($this->expectedResult), $value);
                }, function (): never {
                    Assert::fail('The dish is not valid');
                });

                $recipe = $recipe->given(new DishClass(StringObject::class, $promise));

                $this->recipe = $recipe;

                return $this;
            }
        };
    }

    /**
     * @Given I have a plan to lowercase value in mapping to get :expectedResult
     */
    public function iHaveAPlanToLowerCaseValueInMapping(string $expectedResult): void
    {
        $this->plan = new class ($expectedResult) implements PlanInterface {
            private ?BaseRecipeInterface $recipe = null;

            public function __construct(
                public string $expectedResult = 'abcdef'
            ) {
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

            public function fill(RecipeInterface $recipe): PlanInterface
            {
                $recipe = $recipe->require(new Ingredient('string', 'part1'));
                $recipe = $recipe->cook(
                    function (ChefInterface $chef, string $part1, string $part2): void  {
                        $chef->updateWorkPlan(['string' => $part1 . $part2]);
                    },
                    'concatenate',
                    [
                        'part2' => new Value('FgHIjKl'),
                    ],
                    1
                );

                $recipe = $recipe->cook(
                    fn (ChefInterface $chef, string $string): ChefInterface => $chef->updateWorkPlan([
                        'string' => strtolower($string),
                    ]),
                    'finish',
                    [],
                    2
                );

                $recipe = $recipe->cook(
                    fn (string $string, ChefInterface $chef): ChefInterface => $chef->finish(new StringObject($string)),
                    'createObject',
                    [],
                    2
                );

                $promise = new Promise(function ($value): void {
                    Assert::assertInstanceOf(StringObject::class, $value);
                    Assert::assertEquals(new StringObject($this->expectedResult), $value);
                }, function (): never {
                    Assert::fail('The dish is not valid');
                });

                $recipe = $recipe->given(new DishClass(StringObject::class, $promise));

                $this->recipe = $recipe;

                return $this;
            }
        };
    }

    /**
     * @Given I have an editable plan to lowercase value in mapping to get :expectedResult
     */
    public function iHaveAEditablePlanToLowerCaseValueInMapping($expectedResult): void
    {
        $this->plan = new class ($expectedResult) implements EditablePlanInterface {
            use EditablePlanTrait;

            public function __construct(
                public string $expectedResult = 'abcdef'
            ) {
            }

            protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
            {
                $recipe = $recipe->require(new Ingredient('string', 'part1'));
                $recipe = $recipe->cook(
                    function (ChefInterface $chef, string $part1, string $part2): void  {
                        $chef->updateWorkPlan(['string' => $part1 . $part2]);
                    },
                    'concatenate',
                    [
                        'part2' => new Value('FgHIjKl'),
                    ],
                    1
                );

                $recipe = $recipe->cook(
                    fn (ChefInterface $chef, string $string): ChefInterface => $chef->updateWorkPlan([
                        'string' => strtolower($string),
                    ]),
                    'finish',
                    [],
                    2
                );

                $recipe = $recipe->cook(
                    fn (string $string, ChefInterface $chef): ChefInterface => $chef->finish(new StringObject($string)),
                    'createObject',
                    [],
                    4
                );

                $promise = new Promise(function ($value): void {
                    Assert::assertInstanceOf(StringObject::class, $value);
                    Assert::assertEquals(new StringObject($this->expectedResult), $value);
                }, function (): never {
                    Assert::fail('The dish is not valid');
                });

                return $recipe->given(new DishClass(StringObject::class, $promise));
            }
        };
    }

    /**
     * @Given I have a cookbook with the base trait for date management to get :expectedDate
     * @Given I have a cookbook with the base trait for date management
     */
    public function iHaveACookbookWithTheBaseTraitForDateManagement(?string $expectedDate = ''): void
    {
        $this->notDefaultPlan = false;
        $this->plan = new class ($this, $expectedDate) implements CookbookInterface {
            use BaseCookbookTrait;

            public function __construct(
                private FeatureContext $context,
                public string $expectedDate = '2017-07-01 10:00:00',
            ) {
            }

            protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
            {
                $recipe = $recipe->require(new Ingredient(DateTime::class, DateTime::Class));
                $recipe = $recipe->cook(
                    $this->context->parseMethod('DateTimeImmutable::createFromMutable'),
                    'createImmutable',
                    [],
                    1
                );

                $promise = new Promise(function ($value): void {
                    Assert::assertInstanceOf(DateTimeImmutable::class, $value);
                    Assert::assertEquals(new DateTimeImmutable($this->expectedDate), $value);
                }, function (): never {
                    Assert::fail('The dish is not valid');
                });

                $that = $this;
                $notDefaultPlan = $this->context->notDefaultPlan;
                $recipe = $recipe->cook(
                    function (CookbookInterface $cookbook) use ($that, $notDefaultPlan): void {
                        if (true === $notDefaultPlan) {
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
                    function (ChefInterface $chef, $result): void {
                        $chef->finish($result);
                    },
                    'finish',
                    ['result' => [DateTimeImmutable::Class, DateTime::Class]],
                    10
                );

                $recipe = $recipe->given(new DishClass(DateTimeImmutable::class, $promise));

                $this->recipe = $recipe;

                return $recipe;
            }
        };
    }
    
    /**
     * @Given I have a plan with the base trait for date management to get :expectedDate
     * @Given I have a plan with the base trait for date management
     */
    public function iHaveAPlanWithTheBaseTraitForDateManagement(?string $expectedDate = ''): void
    {
        $this->notDefaultPlan = false;
        $this->plan = new class ($this, $expectedDate) implements PlanInterface {
            use BasePlanTrait;

            public function __construct(
                private FeatureContext $context,
                public string $expectedDate = '2017-07-01 10:00:00',
            ) {
            }

            protected function populateRecipe(RecipeInterface $recipe): RecipeInterface
            {
                $recipe = $recipe->require(new Ingredient(DateTime::class, DateTime::Class));
                $recipe = $recipe->cook(
                    $this->context->parseMethod('DateTimeImmutable::createFromMutable'),
                    'createImmutable',
                    [],
                    1
                );

                $promise = new Promise(function ($value): void {
                    Assert::assertInstanceOf(DateTimeImmutable::class, $value);
                    Assert::assertEquals(new DateTimeImmutable($this->expectedDate), $value);
                }, function (): never {
                    Assert::fail('The dish is not valid');
                });

                $that = $this;
                $notDefaultPlan = $this->context->notDefaultPlan;
                $recipe = $recipe->cook(
                    function (PlanInterface $cookbook) use ($that, $notDefaultPlan): void {
                        if (true === $notDefaultPlan) {
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
                    function (ChefInterface $chef, $result): void {
                        $chef->finish($result);
                    },
                    'finish',
                    ['result' => [DateTimeImmutable::Class, DateTime::Class]],
                    10
                );

                $recipe = $recipe->given(new DishClass(DateTimeImmutable::class, $promise));

                $this->recipe = $recipe;

                return $recipe;
            }
        };
    }

    /**
     * @Then I train the chef with the cookbook
     * @Then I train the chef with the plan
     */
    public function iTrainTheChefWithThePlan(): void
    {
        if (!$this->lastRecipe instanceof RecipeInterface) {
            $this->pushRecipe(new Recipe());
        }

        $this->plan->fill($this->lastRecipe);
        $this->chef->read($this->plan);
    }

    /**
     * @Given I add a step to the recipe to increment the date to get :expectedDate
     */
    public function iAddAStepToTheRecipeToIncrementTheDate(string $expectedDate): void
    {
        $this->pushRecipe(new Recipe());
        $this->pushRecipe(
            $this->lastRecipe->cook(
                function (DateTime $dateTime, ChefInterface $chef): void {
                    $dateTime = $dateTime->modify('+ 4 hours');

                    $chef->updateWorkPlan([
                        DateTime::Class => $dateTime
                    ]);
                },
                'IncrementStep',
                [],
                0,
            )
        );

        $this->plan->expectedDate = $expectedDate;
    }

    /**
     * @Given I add a cookbook instance to the default workplan
     */
    public function iAddACookbookInstanceToTheDefaultWorkplan(): void
    {
        $this->notDefaultPlan = true;
        $this->plan->addToWorkplan(
            CookbookInterface::class,
            new class () implements CookbookInterface {
                public function train(ChefInterface $chef): BaseRecipeInterface
                {
                    return $this;
                }

                public function prepare(array &$workPlan, ChefInterface $chef): BaseRecipeInterface
                {
                    return $this;
                }

                public function validate(mixed $value): BaseRecipeInterface
                {
                    return $this;
                }

                public function fill(RecipeInterface $recipe): CookbookInterface
                {
                    return $this;
                }
            }
        );
    }

    /**
     * @Given I add a plan instance to the default workplan
     */
    public function iAddAPlanInstanceToTheDefaultWorkplan(): void
    {
        $this->notDefaultPlan = true;
        $this->plan->addToWorkplan(
            PlanInterface::class,
            new class () implements PlanInterface {
                public function train(ChefInterface $chef): BaseRecipeInterface
                {
                    return $this;
                }

                public function prepare(array &$workPlan, ChefInterface $chef): BaseRecipeInterface
                {
                    return $this;
                }

                public function validate(mixed $value): BaseRecipeInterface
                {
                    return $this;
                }

                public function fill(RecipeInterface $recipe): PlanInterface
                {
                    return $this;
                }
            }
        );
    }
}
