<?php

/**
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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\Recipe\Ingredient;

use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Ingredient\IngredientInterface;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Ingredient\Ingredient
 */
class IngredientScalarTest extends AbstractIngredientTest
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
