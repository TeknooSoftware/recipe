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

namespace Teknoo\Tests\Recipe;

use DateTime;
use Teknoo\Recipe\Ingredient\TransformableInterface;

class Transformable implements TransformableInterface
{
    public function __construct(
        private readonly mixed $values,
    ) {
    }

    public function transform(): mixed
    {
        return $this->values;
    }

    public static function toTransformable(mixed $value): self
    {
        return new self(new DateTime($value));
    }
}
