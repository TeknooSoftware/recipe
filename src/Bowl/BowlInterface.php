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

namespace Teknoo\Recipe\Bowl;

use RuntimeException;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Recipe\ChefInterface;

/**
 * Interface to define a "bowl". A container with a callable to perform a step in a recipe.
 *
 * A Bowl must be immutable. Any call to execution must not change its state.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
interface BowlInterface extends ImmutableInterface
{
    final public const METHOD_NAME = '_methodName';

    /**
     * To execute the callable contained into the bowl. The bowl instance must automatically map ingredients contained
     * on the Work plan to the callable's arguments :
     * - Maps first on ChefInterface instance (if the argument's class is ChefInterface)
     * - Maps next on the name
     * - Else on the argument's class
     * - Throw a RuntTimeException if a mandatory argument can not be mapped
     *
     * @param array<string, mixed> $workPlan
     * @throws RuntimeException if a required argument can not be mapped.
     */
    public function execute(ChefInterface $chef, array &$workPlan): BowlInterface;
}
