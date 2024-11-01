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

namespace Teknoo\Tests\Recipe\Promise;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Recipe\Promise\WrappedOneCalledPromise;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 *
 */
#[CoversClass(WrappedOneCalledPromise::class)]
final class WrappedOneCalledPromiseTest extends TestCase
{
    public function buildPromise(PromiseInterface $promise): PromiseInterface
    {
        return new WrappedOneCalledPromise($promise);
    }
    public function testNext(): void
    {
        $promise1 = $this->createMock(PromiseInterface::class);
        $promise2 = $this->createMock(PromiseInterface::class);

        $promise1->expects($this->any())->method('next')->willReturn($promise2);

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
    public function testInvoke(): void
    {
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->once())->method('success')->with(['foo', 'bar'])->willReturnSelf();
        $promise->expects($this->once())->method('fetchResult')->willReturn('bar');

        $wp = $this->buildPromise($promise);
        self::assertEquals(
            'bar',
            $wp(['foo', 'bar']),
        );
    }
    public function testSuccess(): void
    {
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->once())->method('success')->with('foo', 'bar')->willReturnSelf();

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
    public function testFail(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $excp = new Exception();

        $promise->expects($this->once())->method('fail')->with($excp)->willReturnSelf();

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
    public function testSetDefaultResult(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('setDefaultResult')->with('foo')->willReturnSelf();

        $wp = $this->buildPromise($promise);

        self::assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->setDefaultResult('foo'),
        );
    }
    public function testFetchResult(): void
    {
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->any())->method('fetchResult')->with('bar')->willReturn('foo');

        $wp = $this->buildPromise($promise);
        self::assertEquals(
            'foo',
            $wp->fetchResult('bar'),
        );
    }
    public function testFetchResultIfCalled(): void
    {
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->any())->method('fetchResultIfCalled')->willReturn('foo');

        $wp = $this->buildPromise($promise);
        self::assertEquals(
            'foo',
            $wp->fetchResultIfCalled(),
        );
    }
}
