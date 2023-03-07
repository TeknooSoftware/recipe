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

namespace Teknoo\Tests\Recipe\Bowl;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\ChefInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Bowl\AbstractDynamicBowl
 * @covers \Teknoo\Recipe\Bowl\DynamicBowl
 * @covers \Teknoo\Recipe\Bowl\BowlTrait
 */
class DynamicBowlMissingCallableMandatoryTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testFailSilentlyIfNoCallbackAvailableInWorkPlan()
    {
        $this->expectException(\RuntimeException::class);
        $bowl = new DynamicBowl(
            'callableToExec',
            true,
            []
        );

        $values = [];
        self::assertInstanceOf(
            DynamicBowl::class,
            $bowl->execute(
                $this->createMock(ChefInterface::class),
                $values
            )
        );
    }
}
