<?php

/**
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\Recipe\Bowl;

use Teknoo\Recipe\Bowl\Bowl;
use Teknoo\Recipe\Bowl\BowlInterface;
use Teknoo\Recipe\ChefInterface;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Bowl\Bowl
 * @covers \Teknoo\Recipe\Bowl\BowlTrait
 */
class BowlFunctionTest extends AbstractBowlTest
{
    protected function getCallable()
    {
        $functionName = 'functionToEvalBowl';

        if (!\function_exists($functionName)) {
            $function = <<<EOF
function $functionName (Teknoo\Recipe\ChefInterface \$chef, string \$bar, \$bar2, \$foo2, \\DateTime \$date, \$_methodName) {
    \$chef->continue([
        'bar' => \$bar,
        'bar2' => \$bar,
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

    /**
     * @inheritDoc
     */
    public function buildBowl(): BowlInterface
    {
        return new Bowl(
            $this->getCallable(),
            $this->getMapping(),
            'bowlClass'
        );
    }

    public function testExecuteWithOptional()
    {
        $chef = $this->createMock(ChefInterface::class);
        $chef->expects(self::once())
            ->method('continue')
            ->with([
                'date' => (new \DateTime('2018-01-01'))->getTimestamp(),
                'opt1' => 123,
                'opt2' => null,
                'opt3' => 'foo',
            ])
            ->willReturnSelf();

        $chef->expects(self::never())
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
