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
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
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
 * @copyright   Copyright (c) 2009-2016 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Bowl\Bowl
 */
class BowlClosureTest extends AbstractBowlTest
{
    protected function getCallable()
    {
        return function (ChefInterface $chef, $bar, $foo2, \DateTime $date) {
            $chef->continue([
                'bar' => $bar,
                'foo2' => $foo2,
                'date' => $date->getTimestamp()
            ]);
        };
    }

    protected function getMapping()
    {
        return ['bar' => 'foo'];
    }

    /**
     * @inheritDoc
     */
    public function buildBowl(): BowlInterface
    {
        return new Bowl(
            $this->getCallable(),
            $this->getMapping()
        );
    }
}
