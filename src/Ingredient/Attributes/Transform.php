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

namespace Teknoo\Recipe\Ingredient\Attributes;

use Attribute;
use RuntimeException;

use function class_exists;
use function is_callable;

/**
 * Class to implement attribute `Transform` to transform an ingredient before to put it into the bowl
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
class Transform
{
    /**
     * @var callable|null
     */
    private $transformer = null;

    public function __construct(
        private ?string $className = null,
        ?callable $transformer = null,
    ) {
        if (!empty($this->className) && !class_exists($this->className)) {
            throw new RuntimeException("Error the required class {$this->className} does not exist");
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
