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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Recipe\Bowl;

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * Bowl to execute a new recipe, with a new trained chef provided by the current chef, but sharing the a clone of the
 * original workplan.
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
class RecipeBowl implements BowlInterface
{
    use ImmutableTrait;

    /**
     * @var RecipeInterface
     */
    private $recipe;

    /**
     * @var int|BowlInterface
     */
    private $repeat;

    /**
     * @var bool
     */
    private $allowToLoop = true;

    /**
     * RecipeBowl constructor.
     * @param RecipeInterface $recipe
     * @param int|BowlInterface $repeat
     */
    public function __construct(RecipeInterface $recipe, $repeat)
    {
        $this->uniqueConstructorCheck();

        $this->recipe = $recipe;
        $this->repeat = $repeat;
    }

    /**
     * @return self
     */
    public function stopLooping(): self
    {
        $this->allowToLoop = false;

        return $this;
    }

    /**
     * @param ChefInterface $chef
     * @param int $counter
     * @param array $workPlan
     * @return bool
     */
    private function checkLooping(ChefInterface $chef, int $counter, array &$workPlan): bool
    {
        if (\is_numeric($this->repeat)) {
            //Strictly less because the step has been executed at least one time.
            return $counter < $this->repeat;
        }

        $loopWorkPlan = \array_merge($workPlan, ['counter' => $counter, 'bowl' => $this]);
        $this->repeat->execute($chef, $loopWorkPlan);

        return (true === $this->allowToLoop);
    }

    /**
     * @inheritDoc
     */
    public function execute(ChefInterface $chef, array &$workPlan): BowlInterface
    {
        $counter = 0;
        do {
            $subchef = $chef->reserveAndBegin($this->recipe);
            $subchef->process([]);
        } while ($this->checkLooping($subchef, ++$counter, $workPlan));

        return $this;
    }
}
