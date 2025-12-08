<?php

/*
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the 3-Clause BSD license
 * it is available in LICENSE file at the root of this package
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richard@teknoo.software so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 *
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Bowl;

use RuntimeException;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Bowl\AbstractDynamicBowl;
use Teknoo\Recipe\Bowl\BowlTrait;
use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\ChefInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AbstractDynamicBowl::class)]
#[CoversClass(DynamicBowl::class)]
final class DynamicBowlBadCallableMandatoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testFailSilentlyIfWrongCallbackAvailableInWorkPlan(): void
    {
        $this->expectException(RuntimeException::class);
        $bowl = new DynamicBowl(
            'callableToExec',
            false,
            []
        );

        $values = ['callableToExec' => 'foo'];
        $this->assertInstanceOf(
            DynamicBowl::class,
            $bowl->execute(
                $this->createStub(ChefInterface::class),
                $values
            )
        );
    }
}
