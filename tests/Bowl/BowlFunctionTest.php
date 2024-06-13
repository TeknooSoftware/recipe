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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversTrait;
use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\Bowl\BowlTrait;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Value;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(Bowl::class)]
#[CoversTrait(BowlTrait::class)]
class BowlFunctionTest extends AbstractBowlTests
{
    protected function getCallable()
    {
        $functionName = 'functionToEvalBowl';

        if (!\function_exists($functionName)) {
            $function = <<<EOF
function $functionName (Teknoo\Recipe\ChefInterface \$chef, string \$bar, \$bar2, \$foo2, \\DateTime \$date, \$_methodName) {
    \$chef->continue([
        'bar' => \$bar,
        'bar2' => \$bar2,
        'foo2' => \$foo2,
        'date' => \$date->getTimestamp(),
        '_methodName' => \$_methodName,
    ]);
}
EOF;
            eval($function);
        }


        return $functionName;
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

    public function testExecuteWithOptional()
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects($this->once())
            ->method('continue')
            ->with([
                'date' => (new \DateTime('2018-01-01'))->getTimestamp(),
                'opt1' => 123,
                'opt2' => null,
                'opt3' => 'foo',
            ])
            ->willReturnSelf();

        $chef->expects($this->never())
            ->method('updateWorkPlan');

        $closure = function (ChefInterface $chef, \DateTime $date, $opt1 = 123, $opt2 = null, $opt3 = null) {
            $chef->continue([
                'date' => $date->getTimestamp(),
                'opt1' => $opt1,
                'opt2' => $opt2,
                'opt3' => $opt3,
            ]);
        };

        $bowl = new Bowl(
            $closure,
            $this->getMapping(),
            'bowlClass'
        );

        $values = [
            'now' => (new \DateTime('2018-01-01')),
            'opt3' => 'foo',
        ];

        self::assertInstanceOf(
            BowlInterface::class,
            $bowl->execute(
                $chef,
                $values
            )
        );
    }
}
