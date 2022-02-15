<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Ingredient;

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;

use function class_exists;
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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
        ?callable $normalizeCallback = null
    ) {
        $this->uniqueConstructorCheck();

        $this->normalizeCallback = $normalizeCallback;
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
        array $workPlan,
        ChefInterface $chef,
        ?IngredientBagInterface $bag = null
    ): IngredientInterface {
        if (!isset($workPlan[$this->name])) {
            $chef->missing($this, "Missing the ingredient {$this->name}");

            return $this;
        }

        $value = $workPlan[$this->name];

        if (!$this->testScalarValue($value, $chef)) {
            return $this;
        }

        if (!$this->testObjectValue($value, $chef)) {
            return $this;
        }

        $normalizedName = $this->getNormalizedName();
        $normalizedValue = $this->normalize($value);

        if ($bag instanceof IngredientBagInterface) {
            $bag->set($normalizedName, $normalizedValue);

            return $this;
        }

        $chef->updateWorkPlan([$normalizedName => $normalizedValue]);

        return $this;
    }
}
