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

use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use Teknoo\Recipe\Bowl\AbstractDynamicBowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\BowlTrait;
use Teknoo\Recipe\Bowl\DynamicBowl;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Value;

use function array_merge;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AbstractDynamicBowl::class)]
#[CoversClass(DynamicBowl::class)]
final class DynamicBowlObjectTest extends AbstractBowlTests
{
    protected function getCallable(): callable
    {
        $object = new class () {
            public function methodToCall(ChefInterface $chef, string $bar, $bar2, $foo2, DateTime $date, $_methodName): void
            {
                $chef->continue([
                    'bar' => $bar,
                    'bar2' => $bar2,
                    'foo2' => $foo2,
                    'date' => $date->getTimestamp(),
                    '_methodName' => $_methodName,
                ]);
            }
        };

        return $object->methodToCall(...);
    }
    protected function getMapping(): array
    {
        return ['bar' => 'foo', 'bar2' => ['bar', 'foo']];
    }
    protected function getValidWorkPlan(): array
    {
        return array_merge(parent::getValidWorkPlan(), ['callableToExec' => $this->getCallable()]);
    }
    protected function getNotValidWorkPlan(): array
    {
        return array_merge(parent::getNotValidWorkPlan(), ['callableToExec' => $this->getCallable()]);
    }
    public function buildBowl(): BowlInterface
    {
        return new DynamicBowl(
            'callableToExec',
            false,
            $this->getMapping(),
            'bowlClass'
        );
    }
    public function buildBowlWithMappingValue(): BowlInterface
    {
        return new DynamicBowl(
            'callableToExec',
            false,
            [
                'bar' => new Value('ValueFoo1'),
                'bar2' => new Value('ValueFoo2'),
            ],
            'bowlClass'
        );
    }
}
