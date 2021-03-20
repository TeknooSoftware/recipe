<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
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

declare(strict_types=1);

namespace Teknoo\Recipe\Bowl;

use Exception;
use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;

/**
 * Default implementation of BowlInterface. A container with a callable to perform a step in a recipe.
 * The callable must be valid. It will not be check in the execute() method, so it's check automatically by the PHP
 * engine thanks to the type hitting in the constructor.
 *
 * With this Bowl, you can map an argument name to another name.
 *
 * @see BowlInterface
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
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
     * @param array<string, string|string[]> $mapping
     */
    public function __construct(callable $callable, array $mapping, string $name = '')
    {
        $this->uniqueConstructorCheck();

        $this->callable = $callable;
        $this->mapping = $mapping;
        $this->name = $name;
    }

    /**
     * @throws Exception
     */
    public function execute(ChefInterface $chef, array &$workPlan): BowlInterface
    {
        $values = $this->extractParameters($this->callable, $chef, $workPlan);

        $callable = $this->callable;
        $callable(...$values);

        return $this;
    }
}
