<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Ingredient;

use BackedEnum;
use DomainException;
use LogicException;
use ReflectionEnum;
use ReflectionFunction;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;
use Throwable;

use function class_exists;
use function enum_exists;
use function function_exists;
use function interface_exists;
use function is_a;
use function is_callable;
use function is_int;
use function is_object;
use function is_string;

/**
 * Base class to define required ingredient needed to start cooking a recipe,
 * initialize or clean them if it's necessary. This class check only the class of each ingredient.
 *
 * @see IngredientInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Ingredient implements IngredientInterface
{
    use ImmutableTrait;

    /**
     * @var callable|null
     */
    private $normalizeCallback;

    private readonly bool $mandatory;

    /**
     * PHP function `is_*` to check a scalar requirement (`int`, `string`, `iterable`...), null for objects.
     * @var callable-string|null
     */
    private readonly ?string $scalarTester;

    /**
     * True when the requirement is a class, an interface or an enum (checked with `is_a`)
     */
    private readonly bool $objectType;

    public function __construct(
        private readonly string $requiredType,
        private readonly ?string $name = null,
        private readonly ?string $normalizedName = null,
        ?callable $normalizeCallback = null,
        bool $mandatory = true,
        private readonly mixed $default = null,
    ) {
        $this->uniqueConstructorCheck();

        $this->scalarTester = self::findScalarTester($this->requiredType);
        $this->objectType = null === $this->scalarTester
            && (class_exists($this->requiredType) || interface_exists($this->requiredType));

        if (
            null === $this->name
            && 'object' !== $this->requiredType
            && null !== $this->scalarTester
        ) {
            throw new LogicException(
                'Error, an ingredient requirement without name is allowed only for object and enum',
            );
        }

        $this->normalizeCallback = $normalizeCallback;

        if (null !== $this->default) {
            $mandatory = false;
        }

        $this->mandatory = $mandatory;

        if (
            null === $this->normalizeCallback
            && enum_exists($this->requiredType)
            && is_a($this->requiredType, BackedEnum::class, true)
            && (new ReflectionEnum($this->requiredType))->isBacked()
        ) {
            $this->normalizeCallback = function (mixed $value) {
                if (!$value instanceof BackedEnum && (is_string($value) || is_int($value))) {
                    $value = ($this->requiredType)::from($value);
                }

                return $value;
            };
        }
    }

    /**
     * To find the PHP function `is_*` able to check a scalar requirement (`is_int`, `is_string`, `is_iterable`...).
     * Only functions with a single required argument are type checkers (`is_a` or `is_subclass_of` are not).
     *
     * @return callable-string|null
     */
    private static function findScalarTester(string $requiredType): ?string
    {
        $function = 'is_' . $requiredType;
        if (!function_exists($function)) {
            return null;
        }

        if (1 !== (new ReflectionFunction($function))->getNumberOfRequiredParameters()) {
            return null;
        }

        return $function;
    }

    private function getNormalizedName(): string
    {
        if (empty($this->normalizedName)) {
            return (string) ($this->name ?? $this->requiredType);
        }

        return $this->normalizedName;
    }

    private function testScalarValue(mixed &$value, ChefInterface $chef): bool
    {
        //An optional ingredient without value in the workplan (and without default value) is accepted as null
        if (null === $value && !$this->mandatory) {
            return true;
        }

        if (null !== $this->scalarTester && !($this->scalarTester)($value)) {
            $chef->missing($this, "The ingredient {$this->name} must be a {$this->requiredType}");

            return false;
        }

        return true;
    }

    private function testObjectValue(mixed &$value, ChefInterface $chef): bool
    {
        //Scalar requirement (already checked) or not a class/interface/enum : nothing to check here
        if (!$this->objectType) {
            return true;
        }

        //A backed enum with a normalizer accepts scalar values, converted by the normalizer
        if (enum_exists($this->requiredType) && null !== $this->normalizeCallback) {
            return true;
        }

        //An optional ingredient without value in the workplan (and without default value) is accepted as null
        if (null === $value) {
            return true;
        }

        //With a normalizer, non objects values are passed to it (to build the object), only objects and class names
        //are checked here
        if (null !== $this->normalizeCallback && !is_object($value) && !is_string($value)) {
            return true;
        }

        if ((is_object($value) || is_string($value)) && is_a($value, $this->requiredType, true)) {
            return true;
        }

        $chef->missing($this, "The ingredient {$this->name} must implement {$this->requiredType}");

        return false;
    }

    private function normalize(mixed &$value): mixed
    {
        if (is_callable($this->normalizeCallback)) {
            return ($this->normalizeCallback)($value);
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $workPlan
     */
    private function getValueFromWorkPlan(
        string $valueName,
        array &$workPlan,
        mixed $defaultValue = null,
    ): mixed {
        $found = isset($workPlan[$valueName]);
        $value = $workPlan[$valueName] ?? $defaultValue;

        if (
            !$found
            && null === $this->name
            && (
                class_exists($this->requiredType)
                || interface_exists($this->requiredType)
                || enum_exists($this->requiredType)
            )
        ) {
            foreach ($workPlan as &$item) {
                if (is_object($item) && is_a($item, $this->requiredType)) {
                    $found = true;
                    $value = $item;
                }
            }
        }

        if (
            false === $found
            && true === $this->mandatory
        ) {
            throw new DomainException("Missing the ingredient {$valueName}");
        }

        if (!$found && !empty($this->default)) {
            $value = $this->default;
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $workPlan
     */
    public function prepare(
        array &$workPlan,
        ChefInterface $chef,
        ?IngredientBagInterface $bag = null,
    ): IngredientInterface {
        $valueName = $this->name ?? $this->requiredType;

        try {
            $value = $this->getValueFromWorkPlan(
                valueName: $valueName,
                workPlan: $workPlan,
                defaultValue: $this->default,
            );
        } catch (DomainException $exception) {
            $chef->missing($this, $exception->getMessage());

            return $this;
        }

        if (!$this->testScalarValue(value: $value, chef: $chef)) {
            return $this;
        }

        if (!$this->testObjectValue(value: $value, chef: $chef)) {
            return $this;
        }

        $normalizedName = $this->getNormalizedName();
        try {
            $normalizedValue = $this->normalize($value);
        } catch (Throwable $error) {
            $chef->missing($this, "The ingredient {$valueName} can not be normalized : {$error->getMessage()}");

            return $this;
        }

        if ($bag instanceof IngredientBagInterface) {
            $bag->set(name: $normalizedName, value: $normalizedValue);

            return $this;
        }

        $chef->updateWorkPlan([$normalizedName => $normalizedValue]);

        return $this;
    }
}
