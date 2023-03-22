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

use Exception;
use Fiber;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;

/**
 * Fiber implementation of BowlInterface. A container with a callable to perform a step in a recipe.
 * The callable must be valid. It will not be check in the execute() method, so it's check automatically by the PHP
 * engine thanks to the type hitting in the constructor.
 *
 * With this Bowl, you can map an argument name to another name. The fiber created to wrap and run the callable can
 * be passed as parameter to the callable
 *
 * @see BowlInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class FiberBowl implements BowlInterface
{
    use ImmutableTrait;
    use BowlTrait;

    /**
     * Valid callable contained in thos bawl.
     *
     * @var callable
     */
    private $callable;

    /**
     * @param array<string, string|string[]> $mapping
     */
    public function __construct(
        callable $callable,
        private readonly array $mapping,
        private readonly string $name = '',
    ) {
        $this->uniqueConstructorCheck();

        $this->callable = $callable;
    }

    /**
     * @throws Exception
     */
    public function execute(
        ChefInterface $chef,
        array &$workPlan,
        ?CookingSupervisorInterface $cookingSupervisor = null,
    ): BowlInterface {
        $fiber = new Fiber($this->callable);
        $values = $this->extractParameters($this->callable, $chef, $workPlan, $fiber, $cookingSupervisor);

        if (null !== $cookingSupervisor) {
            $cookingSupervisor->supervise($fiber);
        }

        $fiber->start(...$values);

        return $this;
    }
}
