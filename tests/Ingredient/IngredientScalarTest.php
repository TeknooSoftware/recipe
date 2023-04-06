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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Ingredient;

use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Ingredient\IngredientInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Recipe\Ingredient\Ingredient
 */
class IngredientScalarTest extends AbstractIngredientTests
{
    /**
     * @inheritDoc
     */
    public function buildIngredient($requiredType = 'string', $name='IngName'): IngredientInterface
    {
        return new Ingredient($requiredType, $name);
    }

    /**
     * @inheritDoc
     */
    public function getWorkPlanValid(): array
    {
        return [
            'IngName' => 'fooBar'
        ];
    }

    /**
     * @inheritDoc
     */
    public function getWorkPlanInvalidMissing(): array
    {
        return [
            'foo' => 'fooBar'
        ];
    }


    /**
     * @inheritDoc
     */
    public function getWorkPlanInvalidNotInstanceOf(): array
    {
        return [
            'IngName' => 123
        ];
    }

    /**
     * @inheritDoc
     */
    public function getWorkPlanInjected(): array
    {
        return [
            'IngName' => 'fooBar'
        ];
    }
}
