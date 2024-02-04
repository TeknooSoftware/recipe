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
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class IngredientWithCondition extends Ingredient
{
    /**
     * @var callable
     */
    private $conditionCallback;

    public function __construct(
        callable $conditionCallback,
        string $requiredType,
        ?string $name,
        ?string $normalizedName = null,
        ?callable $normalizeCallback = null,
        bool $mandatory = true,
        mixed $default = null,
    ) {
        parent::__construct(
            requiredType: $requiredType,
            name: $name,
            normalizedName: $normalizedName,
            normalizeCallback: $normalizeCallback,
            mandatory: $mandatory,
            default: $default,
        );

        $this->conditionCallback = $conditionCallback;
    }

    /**
     * @param array<string, mixed> $workPlan
     */
    public function prepare(
        array &$workPlan,
        ChefInterface $chef,
        ?IngredientBagInterface $bag = null
    ): IngredientInterface {
        if (!($this->conditionCallback)($workPlan, $chef, $bag)) {
            return $this;
        }

        return parent::prepare($workPlan, $chef, $bag);
    }
}
