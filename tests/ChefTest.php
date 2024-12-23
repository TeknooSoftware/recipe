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
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe;

use PHPUnit\Framework\MockObject\MockObject;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\Chef\Cooking;
use Teknoo\Recipe\Chef\Free;
use Teknoo\Recipe\Chef\Trained;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(Chef::class)]
#[CoversClass(Trained::class)]
#[CoversClass(Free::class)]
#[CoversClass(Cooking::class)]
final class ChefTest extends AbstractChefTests
{
    public function buildChef(?ChefInterface $topChef = null): ChefInterface
    {
        return new Chef(null, $topChef);
    }
    public function testReadInConstructor(): void
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $recipe->expects($this->once())
            ->method('train')
            ->willReturnSelf();

        self::assertInstanceOf(
            ChefInterface::class,
            new Chef($recipe)
        );
    }
    public function testErrorWithCatcherWithTopChef(): void
    {
        $topChef = $this->createMock(Chef::class);
        $topChefCalled = [];
        $topChef->expects($this->any())
            ->method('__call')
            ->willReturnCallback(function ($name) use ($topChef, &$topChefCalled): MockObject {
                $topChefCalled[$name] = true;

                return $topChef;
            });

        $chef = $this->buildChef($topChef);
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl): MockObject {
                $called = true;
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->error(
                        new Exception('foo')
                    )
                );

                return $bowl;
            });

        $errorBowl = $this->createMock(BowlInterface::class);
        $errorBowl->expects($this->once())
            ->method('execute')
            ->willReturnSelf();

        $chef->followSteps([$bowl], [$errorBowl]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );

        self::assertTrue($called);

        self::assertArrayHasKey('callErrors', $topChefCalled);
    }
    public function testErrorWithCatcherWithTopChefButErrorReportingIsStopped(): void
    {
        $topChef = $this->createMock(Chef::class);
        $topChefCalled = [];
        $topChef->expects($this->any())
            ->method('__call')
            ->willReturnCallback(function ($name) use ($topChef, &$topChefCalled): MockObject {
                $topChefCalled[$name] = true;

                return $topChef;
            });

        $chef = $this->buildChef($topChef);
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects($this->once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl): MockObject {
                $called = true;
                $chef->stopErrorReporting();
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->error(
                        new Exception('foo')
                    )
                );

                return $bowl;
            });

        $errorBowl = $this->createMock(BowlInterface::class);
        $errorBowl->expects($this->once())
            ->method('execute')
            ->willReturnSelf();

        $chef->followSteps([$bowl], [$errorBowl]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo' => 'bar'])
        );

        self::assertTrue($called);

        self::assertArrayNotHasKey('callErrors', $topChefCalled);
    }
}
