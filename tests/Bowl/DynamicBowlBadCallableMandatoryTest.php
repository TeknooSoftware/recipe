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

namespace Teknoo\Tests\Recipe\Bowl;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\ChefInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Recipe\Bowl\AbstractDynamicBowl
 * @covers \Teknoo\Recipe\Bowl\DynamicBowl
 * @covers \Teknoo\Recipe\Bowl\BowlTrait
 */
class DynamicBowlBadCallableMandatoryTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testFailSilentlyIfWrongCallbackAvailableInWorkPlan()
    {
        $this->expectException(\RuntimeException::class);
        $bowl = new DynamicBowl(
            'callableToExec',
            false,
            []
        );

        $values = ['callableToExec' => 'foo'];
        self::assertInstanceOf(
            DynamicBowl::class,
            $bowl->execute(
                $this->createMock(ChefInterface::class),
                $values
            )
        );
    }
}
