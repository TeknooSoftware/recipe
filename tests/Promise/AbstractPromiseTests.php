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

use Teknoo\Immutable\Exception\ImmutableException;
use Teknoo\Recipe\Promise\PromiseInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractPromiseTests extends TestCase
{
    abstract public function buildPromise($onSuccess, $onFail, bool $allowNext = false): PromiseInterface;

    public function testConstructorBadSuccessCallable()
    {
        $this->expectException(\Throwable::class);
        $this->buildPromise('fooBar', function () {
        });
    }

    public function testConstructorBadFailCallable()
    {
        $this->expectException(\Throwable::class);
        $this->buildPromise(function () {
        }, 'fooBar');
    }

    public function testConstructor()
    {
        self::assertInstanceOf(
            PromiseInterface::class,
            $this->buildPromise(function () {
            }, function () {
            })
        );
    }

    public function testConstructorAtNull()
    {
        self::assertInstanceOf(
            PromiseInterface::class,
            $this->buildPromise(null, null)
        );
    }

    public function testConstructorImmutable()
    {
        $this->expectException(\Error::class);
        $this->buildPromise(
            function () {
            },
            function () {
            }
        )->__construct(
            function () {
            },
            function () {
            }
        );
    }

    public function testNextSetNotCallable()
    {
        $this->expectException(\Throwable::class);
        $this->buildPromise(function () {
        }, function () {
        })->next('fooBar');
    }

    public function testNextSetNull()
    {
        $promise = $this->buildPromise(
            function () {
            }, function () {
        },
            true
        );
        $nextPromise = $promise->next(null);

        self::assertInstanceOf(PromiseInterface::class, $nextPromise);
        self::assertNotSame($promise, $nextPromise);
    }

    public function testNextSetCallable()
    {
        $promise = $this->buildPromise(function () {
        }, function () {
        }, true);
        $nextPromise = $promise->next($this->createMock(PromiseInterface::class));

        self::assertInstanceOf(PromiseInterface::class, $nextPromise);
        self::assertNotSame($promise, $nextPromise);
    }

    public function testNextSetCallableNotAllowed()
    {
        $promise = $this->buildPromise(
            function () {
            },
            function () {
            },
            false
        );

        $this->expectException(\RuntimeException::class);
        $promise->next($this->createMock(PromiseInterface::class));
    }

    public function testSuccess()
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function ($result) use (&$called) {
                $called = true;
                self::assertEquals('foo', $result);
            },
            function () {
                self::fail('Error, fail callback must not be called');
            },
            true
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->success('foo')
        );

        self::assertTrue($called, 'Error the success callback must be called');

        $promiseWithoutSuccessCallback = $this->buildPromise(
            null,
            function () {
                self::fail('Error, fail callback must not be called');
            }
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithoutSuccessCallback->success('foo')
        );
    }

    public function testSuccessWithNextNotDefined()
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function ($result, $next) use (&$called) {
                $called = true;
                self::assertEquals('foo', $result);
                self::assertInstanceOf(
                    PromiseInterface::class,
                    $next
                );
                $next->success($result);
            },
            function () {
                self::fail('Error, fail callback must not be called');
            },
            true
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->success('foo')
        );

        self::assertTrue($called, 'Error the success callback must be called');
    }

    public function testSuccessWithNextNotAllowed()
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function ($result, $next = null) use (&$called) {
                $called = true;
                self::assertEquals('foo', $result);
                self::assertNull($next);
            },
            function () {
                self::fail('Error, fail callback must not be called');
            },
            false
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->success('foo')
        );

        self::assertTrue($called, 'Error the success callback must be called');
    }

    public function testSuccessWithNexDefined()
    {
        $called = 0;
        $promiseWithCallback = $this->buildPromise(
            function ($result, $next) use (&$called) {
                $called++;
                self::assertEquals('foo', $result);
                self::assertInstanceOf(
                    PromiseInterface::class,
                    $next
                );
                $next->success($result);
            },
            function () {
                self::fail('Error, fail callback must not be called');
            },
            true
        );

        $promiseWithCallback = $promiseWithCallback->next($promiseWithCallback);

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->success('foo')
        );

        self::assertEquals(2, $called, 'Error the success callback must be called');
    }

    public function testFail()
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function () {
                self::fail('Error, success callback must not be called');
            },
            function ($result) use (&$called) {
                $called = true;
                self::assertEquals(new \Exception('fooBar'), $result);
            }
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->fail(new \Exception('fooBar'))
        );

        self::assertTrue($called, 'Error the success callback must be called');

        $promiseWithoutSuccessCallback = $this->buildPromise(
            function () {
                self::fail('Error, success callback must not be called');
            },
            null
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithoutSuccessCallback->fail(new \Exception('fooBar'))
        );
    }

    public function testFailWithNextNotDefined()
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function () {
                self::fail('Error, success callback must not be called');
            },
            function ($result, $next) use (&$called) {
                $called = true;
                self::assertEquals(new \Exception('fooBar'), $result);
                self::assertInstanceOf(
                    PromiseInterface::class,
                    $next
                );
                $next->fail($result);
            },
            true
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->fail(new \Exception('fooBar'))
        );

        self::assertTrue($called, 'Error the success callback must be called');
    }

    public function testFailWithNextNotAllowed()
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function () {
                self::fail('Error, success callback must not be called');
            },
            function ($result, $next = null) use (&$called) {
                $called = true;
                self::assertEquals(new \Exception('fooBar'), $result);
                self::assertNull($next);
            },
            false
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->fail(new \Exception('fooBar'))
        );

        self::assertTrue($called, 'Error the success callback must be called');
    }

    public function testFailWithNextDefined()
    {
        $called = 0;
        $promiseWithCallback = $this->buildPromise(
            function () {
                self::fail('Error, success callback must not be called');
            },
            function ($result, $next) use (&$called) {
                $called++;
                self::assertEquals(new \Exception('fooBar'), $result);
                self::assertInstanceOf(
                    PromiseInterface::class,
                    $next
                );
                $next->fail($result);
            },
            true
        );

        $promiseWithCallback = $promiseWithCallback->next($promiseWithCallback);

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->fail(new \Exception('fooBar'))
        );

        self::assertEquals(2, $called, 'Error the success callback must be called');
    }

    public function testFetchResultNotCalled()
    {
        $this->expectException(\RuntimeException::class);
        $this->buildPromise(function () {
        }, function () {
        })->fetchResult();
    }

    public function testFetchResultCalledWithNullFunction()
    {
        $promise = $this->buildPromise(null, null);

        $promise->success();
        self::assertNull($promise->fetchResult());

        $promise->fail(new \Exception('foo'));
        self::assertNull($promise->fetchResult());
    }

    public function testFetchResultCalled()
    {
        $promise = $this->buildPromise(fn() => 'foo', fn() => 'bar');

        $promise->success();
        self::assertEquals('foo', $promise->fetchResult());

        $promise->fail(new \Exception('foo'));
        self::assertEquals('bar', $promise->fetchResult());
    }

    public function testFetchResultIfCalledNotCalled()
    {
        self::assertEquals(
            'default',
            $this->buildPromise(function () {
            }, function () {
            })->fetchResultIfCalled('default')
        );
    }

    public function testFetchResultIfCalledCalledWithNullFunction()
    {
        $promise = $this->buildPromise(null, null);

        $promise->success();
        self::assertNull($promise->fetchResultIfCalled('default'));

        $promise->fail(new \Exception('foo'));
        self::assertNull($promise->fetchResultIfCalled('default'));
    }

    public function testFetchResultIfCalledCalled()
    {
        $promise = $this->buildPromise(fn() => 'foo', fn() => 'bar');

        $promise->success();
        self::assertEquals('foo', $promise->fetchResultIfCalled('default'));

        $promise->fail(new \Exception('foo'));
        self::assertEquals('bar', $promise->fetchResultIfCalled('default'));
    }

    public function testFetchResultWithNestedPromise()
    {
        $promiseNested = $this->buildPromise(fn() => 'foo', fn() => 'bar');
        $promise = $this->buildPromise(
            function (PromiseInterface $next) {
                $next->success('foo');
            },
            function (\Throwable $error, PromiseInterface $next) {
                $next->fail($error);
            }
            , true
        );

        $promise = $promise->next($promiseNested);
        $promise->success();
        self::assertEquals('foo', $promise->fetchResult());

        $promise->fail(new \Exception('foo'));
        self::assertEquals('bar', $promise->fetchResult());
    }

    public function testFetchResultWithNonCalledNestedPromise()
    {
        $promiseNested = $this->buildPromise(fn() => 'foo', fn() => 'bar');
        $promise = $this->buildPromise(
            function (PromiseInterface $next) {
            },
            function (\Throwable $error, PromiseInterface $next) {
            }
            , true
        );

        $promise = $promise->next($promiseNested);
        $promise->success();
        self::assertNull($promise->fetchResult());

        $promise->fail(new \Exception('foo'));
        self::assertNull($promise->fetchResult());
    }

    public function testFetchResultWithNonCalledNestedPromiseWithAutoCall()
    {
        $promiseNested = $this->buildPromise(fn() => 'foo', fn() => 'bar');
        $promise = $this->buildPromise(
            function (PromiseInterface $next) {
            },
            function (\Throwable $error, PromiseInterface $next) {
            }
            , true
        );

        $promise = $promise->next($promiseNested, true);
        $promise->success();
        self::assertEquals('foo', $promise->fetchResult());

        $promise->fail(new \Exception('foo'));
        self::assertEquals('bar', $promise->fetchResult());
    }

    public function testWithSeveralNextWithoutAutoCall()
    {
        $pm1 = $this->buildPromise(
            fn(int $value): int => $value * 2,
            fn(\Throwable $error) => 0,
            true
        );

        $pm1 = $pm1->next(
            $this->buildPromise(
                fn(int $value): int => $value * 3,
                fn(\Throwable $error) => 1,
                true
            )
        );

        $pm1 = $pm1->next(
            $this->buildPromise(
                fn(int $value): int => $value - 2,
                fn(\Throwable $error) => 1,
                true
            )
        );

        self::assertEquals(
            6,
            $pm1->success(3)->fetchResult(),
        );
    }

    public function testWithSeveralNextWithAutoCall()
    {
        $pm1 = $this->buildPromise(
            fn (int $value): int => $value * 2,
            fn (\Throwable $error) => 0,
            true
        );

        $pm1 = $pm1->next(
            promise: $this->buildPromise(
                fn (int $value): int => $value * 3,
                fn (\Throwable $error) => 1,
                true
            ),
            autoCall: true,
        );

        $pm1 = $pm1->next(
            promise: $this->buildPromise(
                fn (int $value): int => $value - 2,
                fn (\Throwable $error) => 1
            ),
            autoCall: true,
        );

        self::assertEquals(
            16,
            $pm1->success(3)->fetchResult(),
        );
    }
}
