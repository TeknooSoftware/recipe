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

namespace Teknoo\Tests\Recipe\Bowl;

use DateTime;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Value;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 * @covers \Teknoo\Recipe\Bowl\Bowl
 * @covers \Teknoo\Recipe\Bowl\BowlTrait
 */
class BowlObjectTest extends AbstractBowlTests
{
    protected function getCallable()
    {
        $object = new class () {
            public function methodToCall(ChefInterface $chef, string $bar, $bar2, $foo2, DateTime $date, $_methodName)
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

        return [$object, 'methodToCall'];
    }

    protected function getMapping()
    {
        return ['bar' => 'foo', 'bar2' => ['bar', 'foo']];
    }

    public function buildBowl(): BowlInterface
    {
        return new Bowl(
            $this->getCallable(),
            $this->getMapping(),
            'bowlClass'
        );
    }

    public function buildBowlWithMappingValue(): BowlInterface
    {
        return new Bowl(
            $this->getCallable(),
            [
                'bar' => new Value('ValueFoo1'),
                'bar2' => new Value('ValueFoo2'),
            ],
            'bowlClass'
        );
    }
}
