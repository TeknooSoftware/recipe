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
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\Recipe\Promise;

use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Recipe\Promise\WrappedOneCalledPromise;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\Recipe\Promise\WrappedOneCalledPromise
 */
class WrappedOneCalledPromiseTest extends TestCase
{
    public function buildPromise(PromiseInterface $promise): PromiseInterface
    {
        return new WrappedOneCalledPromise($promise);
    }

    public function testNext()
    {
        $promise1 = $this->createMock(PromiseInterface::class);
        $promise2 = $this->createMock(PromiseInterface::class);

        $promise1->expects(self::any())->method('next')->willReturn($promise2);

        $wp = $this->buildPromise($promise1);
        $wp2 = $wp->next(
            $this->createMock(PromiseInterface::class)
        );

        self::assertNotSame($wp, $wp2);
        self::assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp2,
        );
    }

    public function testSuccess()
    {
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::once())->method('success')->with('foo', 'bar')->willReturnSelf();

        $wp = $this->buildPromise($promise);
        self::assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->success('foo', 'bar'),
        );
        self::assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->success('foo', 'bar'),
        );
    }

    public function testFail()
    {
        $promise = $this->createMock(PromiseInterface::class);
        $excp = new \Exception();

        $promise->expects(self::once())->method('fail')->with($excp)->willReturnSelf();

        $wp = $this->buildPromise($promise);
        self::assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->fail($excp),
        );
        self::assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->fail($excp),
        );
    }

    public function testFetchResult()
    {
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::any())->method('fetchResult')->willReturn('foo');

        $wp = $this->buildPromise($promise);
        self::assertEquals(
            'foo',
            $wp->fetchResult(),
        );
    }

    public function testFetchResultIfCalled()
    {
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects(self::any())->method('fetchResultIfCalled')->with('bar')->willReturn('foo');

        $wp = $this->buildPromise($promise);
        self::assertEquals(
            'foo',
            $wp->fetchResultIfCalled('bar'),
        );
    }
}
