<?php

declare(strict_types=1);

/**
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
 * @copyright   Copyright (c) 2009-2019 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Recipe\Bowl;

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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
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
     * To initialize the bowl, the type hitting will check the callable.
     *
     * @param callable $callable
     * @param array $mapping
     * @param string $name
     */
    public function __construct(callable $callable, array $mapping, string $name = '')
    {
        $this->uniqueConstructorCheck();

        $this->callable = $callable;
        $this->mapping = $mapping;
        $this->name = $name;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function execute(ChefInterface $chef, array &$workPlan): BowlInterface
    {
        $values = $this->extractParameters($this->callable, $chef, $workPlan);

        $callable = $this->callable;
        $callable(...$values);

        return $this;
    }
}
