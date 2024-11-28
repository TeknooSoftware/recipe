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

namespace Teknoo\Recipe\Ingredient;

use BackedEnum;
use DomainException;
use LogicException;
use ReflectionEnum;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;
use Throwable;

use function class_exists;
use function enum_exists;
use function interface_exists;
use function is_a;
use function is_callable;
use function is_object;
use function is_string;
use function is_subclass_of;

/**
 * Base class to define required ingredient needed to start cooking a recipe,
 * initialize or clean them if it's necessary. This class check only the class of each ingredient.
 *
 * @see IngredientInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
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

    public function __construct(
        private readonly string $requiredType,
        private readonly ?string $name = null,
        private readonly ?string $normalizedName = null,
        ?callable $normalizeCallback = null,
        bool $mandatory = true,
        private readonly mixed $default = null,
    ) {
        $this->uniqueConstructorCheck();

        if (
            null === $this->name
            && 'object' !== $this->requiredType
            && is_callable('is_' . $this->requiredType)
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
            && (new ReflectionEnum($this->requiredType))->isBacked()
        ) {
            $this->normalizeCallback = function (mixed $value) {
                if (!$value instanceof BackedEnum) {
                    $value = ($this->requiredType)::from($value);
                }

                return $value;
            };
        }
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
        $isMethod = 'is_' . $this->requiredType;

        if (is_callable($isMethod) && !$isMethod($value)) {
            $chef->missing($this, "The ingredient {$this->name} must be a {$this->requiredType}");

            return false;
        }

        return true;
    }

    private function testObjectValue(mixed &$value, ChefInterface $chef): bool
    {
        if (
            class_exists($this->requiredType)
            && (!enum_exists($this->requiredType) || null === $this->normalizeCallback)
            && (is_object($value) || is_string($value))
            && !is_a($value, $this->requiredType, true)
            && !is_subclass_of($value, $this->requiredType)
        ) {
            $chef->missing($this, "The ingredient {$this->name} must implement {$this->requiredType}");

            return false;
        }

        return true;
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
