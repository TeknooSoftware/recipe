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
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Ingredient;

use ReflectionEnum;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;
use Throwable;

use function class_exists;
use function enum_exists;
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
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Ingredient implements IngredientInterface
{
    use ImmutableTrait;

    /**
     * @var callable|null
     */
    private $normalizeCallback;

    public function __construct(
        private readonly string $requiredType,
        private readonly string $name,
        private readonly ?string $normalizedName = null,
        ?callable $normalizeCallback = null,
        private readonly bool $mandatory = true,
        private readonly mixed $default = null,
    ) {
        $this->uniqueConstructorCheck();

        $this->normalizeCallback = $normalizeCallback;

        if (
            null === $this->normalizeCallback
            && enum_exists($this->requiredType)
            && (new ReflectionEnum($this->requiredType))->isBacked()
        ) {
            $this->normalizeCallback = ($this->requiredType)::from(...);
        }
    }

    private function getNormalizedName(): string
    {
        if (empty($this->normalizedName)) {
            return $this->name;
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
    public function prepare(
        array &$workPlan,
        ChefInterface $chef,
        ?IngredientBagInterface $bag = null,
    ): IngredientInterface {
        if (
            !isset($workPlan[$this->name])
            && true === $this->mandatory
        ) {
            $chef->missing($this, "Missing the ingredient {$this->name}");

            return $this;
        }

        $value = $this->default;
        if (isset($workPlan[$this->name])) {
            $value = $workPlan[$this->name];
        }

        if (!$this->testScalarValue($value, $chef)) {
            return $this;
        }

        if (!$this->testObjectValue($value, $chef)) {
            return $this;
        }

        $normalizedName = $this->getNormalizedName();
        try {
            $normalizedValue = $this->normalize($value);
        } catch (Throwable $error) {
            $chef->missing($this, "The ingredient {$this->name} can not be normalized : {$error->getMessage()}");

            return $this;
        }

        if ($bag instanceof IngredientBagInterface) {
            $bag->set($normalizedName, $normalizedValue);

            return $this;
        }

        $chef->updateWorkPlan([$normalizedName => $normalizedValue]);

        return $this;
    }
}
