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
     * Ingredient constructor.
     * @param string $requiredType
     * @param string $name
     */
    public function __construct(string $requiredType , string $name)
    {
        $this->uniqueConstructorCheck();

        $this->requiredType = $requiredType;
        $this->name = $name;
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

        if (!\is_a($value, $this->requiredType, true) && !\is_subclass_of($value, $this->requiredType)) {
            $chef->missing($this, "The ingredient {$this->name} must implement {$this->requiredType}");

            return $this;
        }

        $chef->updateWorkPlan([$this->name => $value]);

        return $this;
    }
}