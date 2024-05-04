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

namespace Teknoo\Recipe\Bowl;

use Exception;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;
use Teknoo\Recipe\Value;

/**
 * Default implementation of BowlInterface. A container with a callable to perform a step in a recipe.
 * The callable must be valid. It will not be check in the execute() method, so it's check automatically by the PHP
 * engine thanks to the type hitting in the constructor.
 *
 * With this Bowl, you can map an argument name to another name.
 *
 * @see BowlInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Bowl implements BowlInterface
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
     * @param array<string, string|string[]|Value> $mapping
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
        $callable = &$this->callable;
        $callable(...$this->extractParameters($this->callable, $chef, $workPlan, null, $cookingSupervisor));

        return $this;
    }
}
