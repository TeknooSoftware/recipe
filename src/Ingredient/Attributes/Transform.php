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

namespace Teknoo\Recipe\Ingredient\Attributes;

use Attribute;
use Teknoo\Recipe\Ingredient\Exception\WrongClassException;

use function class_exists;

/**
 * Class to implement attribute `Transform` to transform an ingredient before to put it into the bowl
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Transform
{
    /**
     * @var callable|null
     */
    private $transformer = null;

    public function __construct(
        private readonly ?string $className = null,
        ?callable $transformer = null,
    ) {
        if (!empty($this->className) && !class_exists($this->className)) {
            throw new WrongClassException("Error the required class {$this->className} does not exist");
        }

        $this->transformer = $transformer;
    }

    public function getClassName(): ?string
    {
        return $this->className;
    }

    public function getTransformer(): ?callable
    {
        return $this->transformer;
    }
}
