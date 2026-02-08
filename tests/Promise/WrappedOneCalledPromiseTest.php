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

namespace Teknoo\Tests\Recipe\Promise;

use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Recipe\Promise\WrappedOneCalledPromise;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
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
        $promise1 = $this->createStub(PromiseInterface::class);
        $promise2 = $this->createStub(PromiseInterface::class);

        $promise1->method('next')->willReturn($promise2);

        $wp = $this->buildPromise($promise1);
        $wp2 = $wp->next(
            $this->createStub(PromiseInterface::class)
        );

        $this->assertNotSame($wp, $wp2);
        $this->assertInstanceOf(
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
        $this->assertEquals(
            'bar',
            $wp(['foo', 'bar']),
        );
    }
    public function testSuccess(): void
    {
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->once())->method('success')->with('foo', 'bar')->willReturnSelf();

        $wp = $this->buildPromise($promise);
        $this->assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->success('foo', 'bar'),
        );
        $this->assertInstanceOf(
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
        $this->assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->fail($excp),
        );
        $this->assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->fail($excp),
        );
    }
    public function testSetDefaultResult(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())->method('setDefaultResult')->with('foo')->willReturnSelf();

        $wp = $this->buildPromise($promise);

        $this->assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->setDefaultResult('foo'),
        );
    }
    public function testFetchResult(): void
    {
        $promise = $this->createStub(PromiseInterface::class);

        $promise->method('fetchResult')->willReturn('foo');

        $wp = $this->buildPromise($promise);
        $this->assertEquals(
            'foo',
            $wp->fetchResult('bar'),
        );
    }
    public function testFetchResultIfCalled(): void
    {
        $promise = $this->createStub(PromiseInterface::class);

        $promise->method('fetchResultIfCalled')->willReturn('foo');

        $wp = $this->buildPromise($promise);
        $this->assertEquals(
            'foo',
            $wp->fetchResultIfCalled(),
        );
    }

    public function testReset(): void
    {
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->once())->method('reset')->willReturnSelf();

        $wp = $this->buildPromise($promise);
        $this->assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->reset(),
        );
    }

    public function testAllowReuse(): void
    {
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->once())->method('allowReuse')->willReturnSelf();

        $wp = $this->buildPromise($promise);
        $this->assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->allowReuse(),
        );
    }

    public function testProhibitReuse(): void
    {
        $promise = $this->createMock(PromiseInterface::class);

        $promise->expects($this->once())->method('prohibitReuse')->willReturnSelf();

        $wp = $this->buildPromise($promise);
        $this->assertInstanceOf(
            WrappedOneCalledPromise::class,
            $wp->prohibitReuse(),
        );
    }
}
