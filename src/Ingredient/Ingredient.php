<?php

declare(strict_types=1);

/**
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Recipe\Ingredient;

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;

/**
 * Base class to define required ingredient needed to start cooking a recipe,
 * initialize or clean them if it's necessary. This class check only the class of each ingredient.
 *
 * @see IngredientInterface
 *
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
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
     * @var string
     */
    private $requiredType;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $normalizedName;

    /**
     * @var callable|null
     */
    private $normalizeCallback;

    /**
     * Ingredient constructor.
     * @param string $requiredType
     * @param string $name
     * @param string|null $normalizedName
     * @param callable|null $normalizeCallback
     */
    public function __construct(
        string $requiredType,
        string $name,
        string $normalizedName = null,
        callable $normalizeCallback = null
    ) {
        $this->uniqueConstructorCheck();

        $this->requiredType = $requiredType;
        $this->name = $name;
        $this->normalizedName = $normalizedName;
        $this->normalizeCallback = $normalizeCallback;
    }

    /**
     * @return string
     */
    private function getNormalizedName(): string
    {
        if (empty($this->normalizedName)) {
            return $this->name;
        }

        return $this->normalizedName;
    }

    /**
     * @param $value
     * @param ChefInterface $chef
     * @return bool
     */
    private function testScalarValue(&$value, ChefInterface $chef): bool
    {
        $isMethod = 'is_'.$this->requiredType;

        if (\function_exists($isMethod) && !$isMethod($value)) {
            $chef->missing($this, "The ingredient {$this->name} must be a {$this->requiredType}");

            return false;
        }

        return true;
    }

    /**
     * @param $value
     * @param ChefInterface $chef
     * @return bool
     */
    private function testObjectValue(&$value, ChefInterface $chef): bool
    {
        if (\class_exists($this->requiredType)
            && !\is_a($value, $this->requiredType, true)
            && !\is_subclass_of($value, $this->requiredType)) {
            $chef->missing($this, "The ingredient {$this->name} must implement {$this->requiredType}");

            return false;
        }

        return true;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private function normalize(&$value)
    {
        if (\is_callable($this->normalizeCallback)) {
            return ($this->normalizeCallback)($value);
        }

        return $value;
    }

    /**
     * @inheritDoc
     */
    public function prepare(array $workPlan, ChefInterface $chef): IngredientInterface
    {
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

        $chef->updateWorkPlan([$this->getNormalizedName() => $this->normalize($value)]);

        return $this;
    }
}
