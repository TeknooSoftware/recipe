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

namespace Teknoo\Recipe\Plan;

use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Value;

/**
 * Value Object to pass step with mapping to an editable plan
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
class Step
{
    /**
     * @var BowlInterface|callable
     */
    private $step;

    /**
     * @param array<string, string|string[]|Value> $with
     */
    public function __construct(
        BowlInterface | callable $step,
        private readonly array $with = [],
    ) {
        $this->step = $step;
    }

    public function getStep(): callable|BowlInterface
    {
        return $this->step;
    }

    /**
     * @return array<string, string|string[]|Value>
     */
    public function getWith(): array
    {
        return $this->with;
    }
}
