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

use Teknoo\Immutable\ImmutableTrait;
use Teknoo\Recipe\BaseRecipeInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\CookingSupervisorInterface;

use function is_numeric;

/**
 * Abstract bowl class to execute a new recipe, with a new trained chef provided by the current chef, but sharing the a
 * clone of the original workplan.
 *
 * @see BowlInterface
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractRecipeBowl implements BowlInterface
{
    use ImmutableTrait;

    private BaseRecipeInterface $recipe;

    private int | BowlInterface $repeat;

    private bool $allowToLoop = true;

    public function __construct(BaseRecipeInterface $recipe, int | BowlInterface $repeat)
    {
        $this->uniqueConstructorCheck();

        $this->recipe = $recipe;
        $this->repeat = $repeat;
    }

    public function stopLooping(): self
    {
        $this->allowToLoop = false;

        return $this;
    }

    /**
     * @param array<string, mixed> $workPlan
     */
    private function checkLooping(
        ChefInterface $chef,
        int $counter,
        array &$workPlan,
        ?CookingSupervisorInterface $cookingSupervisor,
    ): bool {
        if (is_numeric($this->repeat)) {
            //Strictly less because the step has been executed at least one time.
            return $counter < $this->repeat;
        }

        $loopWorkPlan = ['counter' => $counter, 'bowl' => $this] + $workPlan;
        $this->repeat->execute($chef, $loopWorkPlan, $cookingSupervisor);

        return (true === $this->allowToLoop);
    }

    abstract protected function processToExecution(
        ChefInterface $subchef,
        ?CookingSupervisorInterface $cookingSupervisor,
    ): void;

    public function execute(
        ChefInterface $chef,
        array &$workPlan,
        ?CookingSupervisorInterface $cookingSupervisor = null,
    ): BowlInterface {
        $counter = 0;
        do {
            $subSupervisor = null;
            if (null !== $cookingSupervisor) {
                $subSupervisor = clone $cookingSupervisor;

                $subSupervisor->setParentSupervisor($cookingSupervisor);
            }

            $subchef = $chef->reserveAndBegin($this->recipe, $subSupervisor);
            $subchef->updateWorkPlan($workPlan);
            $this->processToExecution($subchef, $cookingSupervisor);
        } while ($this->checkLooping($subchef, ++$counter, $workPlan, $cookingSupervisor));

        return $this;
    }
}
