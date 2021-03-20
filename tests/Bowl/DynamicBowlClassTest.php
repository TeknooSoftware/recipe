<?php

/**
 * Recipe.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license
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

use Teknoo\Recipe\Bowl\DynamicBowl;
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
 * @covers \Teknoo\Recipe\Bowl\DynamicBowl
 * @covers \Teknoo\Recipe\Bowl\BowlTrait
 */
class DynamicBowlClassTest extends AbstractBowlTest
{
    public static function methodToCall(
        ChefInterface $chef,
        string $bar,
        $foo2,
        \DateTime $date,
        $_methodName,
        self $self
    ) {
        $chef->continue([
            'bar' => $bar,
            'bar2' => $bar,
            'foo2' => $foo2,
            'date' => $date->getTimestamp(),
            '_methodName' => $_methodName,
        ]);
    }

    protected function getCallable()
    {
        return [static::class, 'methodToCall'];
    }

    protected function getMapping()
    {
        return ['bar' => 'foo', 'bar2' => ['bar', 'foo']];
    }

    /**
     * @inheritDoc
     */
    protected function getValidWorkPlan(): array
    {
        return \array_merge(
            parent::getValidWorkPlan(),
            ['callableToExec' => $this->getCallable(), self::class => $this]
        );
    }

    /**
     * @inheritDoc
     */
    protected function getNotValidWorkPlan(): array
    {
        return \array_merge(
            parent::getNotValidWorkPlan(),
            ['callableToExec' => $this->getCallable(), self::class => $this]
        );
    }

    /**
     * @inheritDoc
     */
    public function buildBowl(): BowlInterface
    {
        return new DynamicBowl(
            'callableToExec',
            false,
            $this->getMapping(),
            'bowlClass'
        );
    }
}
