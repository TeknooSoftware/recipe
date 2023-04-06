<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
 * that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Recipe;

use Fiber;
use Throwable;

/**
 * Interface to define a supervisor, dedicated to a chef, able to manage cooking of several steps or subrecipes in same
 * time, thanks to green thread (Fiber) and help the chef to switch between tasks.
 *
 * A supervisor can also manage others supervisors started in subrecipes : Warning, any supervised supervisor must
 * signal to its manager when it has finish to cook, to free resource in the manager. The top manager will call the
 * loop method of supervised on call of switch or loop method, and finish all supervised.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
interface CookingSupervisorInterface
{
    /*
     * To update the parent supervisor managing the current instance
     */
    public function setParentSupervisor(CookingSupervisorInterface $supervisor): CookingSupervisorInterface;

    /**
     * To manage a new fiber created by a Chef. The supervisor will not start the fiber, it must be started in the bowl
     * after register it into the supervisor. (The supervisor is not able to pass good parameters)
     * @param Fiber<mixed, mixed, void, mixed> $fiber
     */
    public function supervise(
        Fiber $fiber,
    ): CookingSupervisorInterface;

    /*
     * To manage, register a supervisor dedicated to a subchef of the chef linked to this supervisor.
     */
    public function manage(
        CookingSupervisorInterface $supervisor,
    ): CookingSupervisorInterface;

    /*
     * To lost a supervised supervisor
     */
    public function free(
        CookingSupervisorInterface $supervisor,
    ): CookingSupervisorInterface;

    /*
     * To resume the next task suspended in the list. A value can be passed, will be returned by
     * `Fiber::suspend()`. If there are no task, this method do nothing.
     */
    public function switch(mixed $value = null): CookingSupervisorInterface;

    /*
     * To resume a specific task in the list, from its name, with an exception.
     * If the task is not suspended, this method do nothing
     */
    public function throw(Throwable $value = null): CookingSupervisorInterface;

    /*
     * Rewind the cursor on the top the list
     */
    public function rewindLoop(): CookingSupervisorInterface;

    /*
     * To iterate once time all fibers in the list and return to the top of the loop.
     * The supervisor will start the loop from the current cursor in the list and ignore previous entries.
     */
    public function loop(mixed $value = null): CookingSupervisorInterface;

    /*
     * To loop on each suspended fibers until all fibers are terminated
     */
    public function finish(mixed $value = null): CookingSupervisorInterface;
}
