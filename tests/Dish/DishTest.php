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

namespace Teknoo\Tests\Recipe\Dish;

use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Teknoo\Recipe\Dish\AbstractDishClass;
use Teknoo\Recipe\Dish\DishClass;
use Teknoo\Recipe\Dish\DishInterface;
use Teknoo\Recipe\Promise\PromiseInterface;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(AbstractDishClass::class)]
#[CoversClass(DishClass::class)]
final class DishTest extends AbstractDishTests
{
    /**
     * @var PromiseInterface
     */
    private ?MockObject $promise = null;
    protected function getExceptedValue(): DateTime
    {
        return new DateTime('2018-01-01');
    }
    protected function getPromise(): MockObject&PromiseInterface
    {
        if (!$this->promise instanceof PromiseInterface) {
            $this->promise = $this->createMock(PromiseInterface::class);
        }

        return $this->promise;
    }
    public function buildDish(): DishInterface
    {
        return new DishClass(
            DateTime::class,
            $this->getPromise()
        );
    }
}
