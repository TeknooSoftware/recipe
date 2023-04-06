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
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe;

use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\RecipeInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richard@teknoo.software)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Recipe\Chef
 * @covers \Teknoo\Recipe\Chef\Cooking
 * @covers \Teknoo\Recipe\Chef\Free
 * @covers \Teknoo\Recipe\Chef\Trained
 */
class ChefTest extends AbstractChefTests
{
    public function buildChef(?ChefInterface $topChef = null): ChefInterface
    {
        return new Chef(null, $topChef);
    }

    public function testReadInConstructor()
    {
        $recipe = $this->createMock(RecipeInterface::class);
        $recipe->expects(self::once())
            ->method('train')
            ->willReturnSelf();

        self::assertInstanceOf(
            ChefInterface::class,
            new Chef($recipe)
        );
    }

    public function testErrorWithCatcherWithTopChef()
    {
        $topChef = $this->createMock(Chef::class);
        $topChefCalled = [];
        $topChef->expects(self::any())
            ->method('__call')
            ->willReturnCallback(function ($name) use ($topChef, &$topChefCalled) {
                $topChefCalled[$name] = true;

                return $topChef;
            });

        $chef = $this->buildChef($topChef);
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl) {
                $called = true;
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->error(
                        new \Exception('foo')
                    )
                );

                return $bowl;
            });

        $errorBowl = $this->createMock(BowlInterface::class);
        $errorBowl->expects(self::once())
            ->method('execute')
            ->willReturnSelf();

        $chef->followSteps([$bowl], [$errorBowl]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );

        self::assertTrue($called);

        self::assertArrayHasKey('callErrors', $topChefCalled);
    }

    public function testErrorWithCatcherWithTopChefButErrorReportingIsStopped()
    {
        $topChef = $this->createMock(Chef::class);
        $topChefCalled = [];
        $topChef->expects(self::any())
            ->method('__call')
            ->willReturnCallback(function ($name) use ($topChef, &$topChefCalled) {
                $topChefCalled[$name] = true;

                return $topChef;
            });

        $chef = $this->buildChef($topChef);
        $chef->read($this->createMock(RecipeInterface::class));

        $called = false;
        $bowl = $this->createMock(BowlInterface::class);
        $bowl->expects(self::once())
            ->method('execute')
            ->willReturnCallback(function () use ($chef, &$called, $bowl) {
                $called = true;
                $chef->stopErrorReporting();
                self::assertInstanceOf(
                    ChefInterface::class,
                    $chef->error(
                        new \Exception('foo')
                    )
                );

                return $bowl;
            });

        $errorBowl = $this->createMock(BowlInterface::class);
        $errorBowl->expects(self::once())
            ->method('execute')
            ->willReturnSelf();

        $chef->followSteps([$bowl], [$errorBowl]);

        self::assertInstanceOf(
            ChefInterface::class,
            $chef->process(['foo'=>'bar'])
        );

        self::assertTrue($called);

        self::assertArrayNotHasKey('callErrors', $topChefCalled);
    }
}
