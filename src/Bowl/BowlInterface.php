<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
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
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe\Bowl;

use BadMethodCallException;
use RuntimeException;
use Teknoo\Immutable\ImmutableInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;

/**
 * Interface to define a "bowl". A container with a callable to perform a step in a recipe.
 *
 * A Bowl must be immutable. Any call to execution must not change its state.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface BowlInterface extends ImmutableInterface
{
    final public const METHOD_NAME = '_methodName';

    /**
     * To execute the callable contained into the bowl. The bowl instance must automatically map ingredients contained
     * on the Work plan to the callable's arguments, in this order :
     * - Reserved instances, from the argument's type : ChefInterface, Fiber (fiber bowls) and
     *   CookingSupervisorInterface
     * - The mapping defined with the bowl : another ingredient's name, a list of names, or a fixed Value
     * - The argument's name, as key of the workplan
     * - The argument's class name, as key of the workplan
     * - An instance of the argument's type in the workplan (also a TransformableInterface instance of the class
     *   defined by the Transform attribute)
     * - The special name `_methodName`, to get the name of the step
     * - The default value of the argument
     * - Throw a BadMethodCallException (a LogicException) if a mandatory argument can not be mapped
     *
     * @param array<string, mixed> $workPlan
     * @throws RuntimeException if the callable can not be executed (missing callable in a dynamic bowl...)
     * @throws BadMethodCallException if a required argument can not be mapped.
     */
    public function execute(
        ChefInterface $chef,
        array &$workPlan,
        ?CookingSupervisorInterface $cookingSupervisor = null,
    ): BowlInterface;
}
