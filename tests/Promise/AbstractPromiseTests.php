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
 * @link        https://teknoo.software/libraries/recipe Project website
 *
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */

declare(strict_types=1);

namespace Teknoo\Tests\Recipe\Promise;

use Error;
use Exception;
use RuntimeException;
use Teknoo\Immutable\Exception\ImmutableException;
use Teknoo\Recipe\Promise\PromiseInterface;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     https://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractPromiseTests extends TestCase
{
    abstract public function buildPromise(
        $onSuccess,
        $onFail,
        bool $allowNext = true,
        bool $callOnFailOnException = true,
    ): PromiseInterface;

    public function testConstructorBadSuccessCallable(): void
    {
        $this->expectException(Throwable::class);
        $this->buildPromise(
            onSuccess: 'fooBar',
            onFail: function (): void {
            },
        );
    }

    public function testConstructorBadFailCallable(): void
    {
        $this->expectException(Throwable::class);
        $this->buildPromise(
            onSuccess: function (): void {
            },
            onFail: 'fooBar',
        );
    }

    public function testConstructor(): void
    {
        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $this->buildPromise(
                onSuccess: function (): void {
                },
                onFail: function (): void {
                },
            ),
        );
    }

    public function testConstructorAtNull(): void
    {
        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $this->buildPromise(onSuccess: null, onFail: null)
        );
    }

    public function testConstructorImmutable(): void
    {
        $this->expectException(Error::class);
        $this->buildPromise(
            onSuccess: function (): void {
            },
            onFail: function (): void {
            },
        )->__construct(
            function (): void {
            },
            function (): void {
            },
        );
    }

    public function testNextSetNotCallable(): void
    {
        $this->expectException(Throwable::class);
        $this->buildPromise(
            onSuccess: function (): void {
            },
            onFail: function (): void {
            },
        )->next('fooBar');
    }

    public function testNextSetNull(): void
    {
        $promise = $this->buildPromise(
            onSuccess: function (): void {
            },
            onFail: function (): void {
            },
            allowNext: true,
        );
        $nextPromise = $promise->next(null);

        self::assertInstanceOf(expected: PromiseInterface::class, actual: $nextPromise);
        self::assertNotSame(expected: $promise, actual: $nextPromise);
    }

    public function testNextSetCallable(): void
    {
        $promise = $this->buildPromise(
            onSuccess: function (): void {
            },
            onFail: function (): void {
            },
            allowNext: true,
        );
        $nextPromise = $promise->next($this->createMock(PromiseInterface::class));

        self::assertInstanceOf(expected: PromiseInterface::class, actual: $nextPromise);
        self::assertNotSame(expected: $promise, actual: $nextPromise);
    }

    public function testNextSetCallableNotAllowed(): void
    {
        $promise = $this->buildPromise(
            onSuccess: function (): void {
            },
            onFail: function (): void {
            },
            allowNext: false
        );

        $this->expectException(RuntimeException::class);
        $promise->next($this->createMock(PromiseInterface::class));
    }

    public function testSuccess(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            onSuccess: function ($result) use (&$called): void {
                $called = true;
                self::assertEquals(expected: 'foo', actual: $result);
            },
            onFail: function (): void {
                self::fail('Error, fail callback must not be called');
            },
            allowNext: true
        );

        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->success('foo')
        );

        self::assertTrue(condition: $called, message: 'Error the success callback must be called');

        $promiseWithoutSuccessCallback = $this->buildPromise(
            onSuccess: null,
            onFail: function (): void {
                self::fail('Error, fail callback must not be called');
            }
        );

        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithoutSuccessCallback->success('foo')
        );
    }

    public function testSuccessWithNextNotDefined(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            onSuccess: function ($result, $next) use (&$called): void {
                $called = true;
                self::assertEquals(expected: 'foo', actual: $result);
                self::assertInstanceOf(
                    expected: PromiseInterface::class,
                    actual: $next
                );
                $next->success($result);
            },
            onFail: function (): void {
                self::fail('Error, fail callback must not be called');
            },
            allowNext: true
        );

        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->success('foo')
        );

        self::assertTrue(condition: $called, message: 'Error the success callback must be called');
    }

    public function testSuccessWithNextNotAllowed(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            onSuccess: function ($result, $next = null) use (&$called): void {
                $called = true;
                self::assertEquals(expected: 'foo', actual: $result);
                self::assertNull($next);
            },
            onFail: function (): void {
                self::fail('Error, fail callback must not be called');
            },
            allowNext: false,
        );

        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->success('foo')
        );

        self::assertTrue(condition: $called, message: 'Error the success callback must be called');
    }

    public function testSuccessWithNexDefined(): void
    {
        $called = 0;
        $promiseWithCallback = $this->buildPromise(
            onSuccess: function ($result, $next) use (&$called): void {
                ++$called;
                self::assertEquals(expected: 'foo', actual: $result);
                self::assertInstanceOf(
                    expected: PromiseInterface::class,
                    actual: $next
                );
                $next->success($result);
            },
            onFail: function (): void {
                self::fail('Error, fail callback must not be called');
            },
            allowNext: true,
        );

        $promiseWithCallback = $promiseWithCallback->next($promiseWithCallback);

        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->success('foo')
        );

        self::assertEquals(expected: 2, actual: $called, message: 'Error the success callback must be called');
    }

    public function testFail(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            onSuccess: function (): void {
                self::fail('Error, success callback must not be called');
            },
            onFail: function ($result) use (&$called): void {
                $called = true;
                self::assertEquals(expected: new Exception('fooBar'), actual: $result);
            },
        );

        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->fail(new Exception('fooBar'))
        );

        self::assertTrue(condition: $called, message: 'Error the success callback must be called');

        $promiseWithoutSuccessCallback = $this->buildPromise(
            onSuccess: function (): void {
                self::fail('Error, success callback must not be called');
            },
            onFail: null
        );

        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithoutSuccessCallback->fail(new Exception('fooBar'))
        );
    }

    public function testFailWithNextNotDefined(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            onSuccess: function (): void {
                self::fail('Error, success callback must not be called');
            },
            onFail: function ($result, $next) use (&$called): void {
                $called = true;
                self::assertEquals(expected: new Exception('fooBar'), actual: $result);
                self::assertInstanceOf(
                    expected: PromiseInterface::class,
                    actual: $next
                );
                $next->fail($result);
            },
            allowNext: true,
        );

        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->fail(new Exception('fooBar'))
        );

        self::assertTrue(condition: $called, message: 'Error the success callback must be called');
    }

    public function testFailWithNextNotAllowed(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            onSuccess: function (): void {
                self::fail('Error, success callback must not be called');
            },
            onFail: function ($result, $next = null) use (&$called): void {
                $called = true;
                self::assertEquals(expected: new Exception('fooBar'), actual: $result);
                self::assertNull($next);
            },
            allowNext: false,
        );

        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->fail(new Exception('fooBar'))
        );

        self::assertTrue(condition: $called, message: 'Error the success callback must be called');
    }

    public function testFailWithNextDefined(): void
    {
        $called = 0;
        $promiseWithCallback = $this->buildPromise(
            onSuccess: function (): void {
                self::fail('Error, success callback must not be called');
            },
            onFail: function ($result, $next) use (&$called): void {
                ++$called;
                self::assertEquals(expected: new Exception('fooBar'), actual: $result);
                self::assertInstanceOf(
                    expected: PromiseInterface::class,
                    actual: $next
                );
                $next->fail($result);
            },
            allowNext: true,
        );

        $promiseWithCallback = $promiseWithCallback->next($promiseWithCallback);

        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->fail(new Exception('fooBar'))
        );

        self::assertEquals(expected: 2, actual: $called, message: 'Error the success callback must be called');
    }

    public function testSetDefaultResult(): void
    {
        self::assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $this->buildPromise(
                onSuccess: function (): void {
                },
                onFail: function (): void {
                }
            )->setDefaultResult('foo')
        );
    }

    public function testFetchResultNotCalled(): void
    {
        self::assertEquals(
            expected: 'default',
            actual: $this->buildPromise(
                onSuccess: function (): void {
                },
                onFail: function (): void {
                },
            )->fetchResult('default')
        );
    }

    public function testFetchResultNotCalledWithCallableAsResult(): void
    {
        self::assertEquals(
            expected: 'default',
            actual: $this->buildPromise(
                onSuccess: function (): void {
                },
                onFail: function (): void {
                },
            )->fetchResult(fn (): string => 'default')
        );
    }

    public function testFetchResultCalledWithNullFunction(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);

        $promise->success();
        self::assertNull($promise->fetchResult('default'));

        $promise->fail(new Exception('foo'));
        self::assertNull($promise->fetchResult('default'));
    }

    public function testFetchResultCalled(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');

        $promise->success();
        self::assertEquals(expected: 'foo', actual: $promise->fetchResult('default'));

        $promise->fail(new Exception('foo'));
        self::assertEquals(expected: 'bar', actual: $promise->fetchResult('default'));
    }

    public function testFetchResultWithNestedPromise(): void
    {
        $promiseNested = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');
        $promise = $this->buildPromise(
            onSuccess: function (PromiseInterface $next): void {
                $next->success('foo');
            },
            onFail: function (Throwable $error, PromiseInterface $next): void {
                $next->fail($error);
            },
            allowNext: true
        );

        $promise = $promise->next($promiseNested);
        $promise->success();
        self::assertEquals(expected: 'foo', actual: $promise->fetchResult());

        $promise->fail(new Exception('foo'));
        self::assertEquals(expected: 'bar', actual: $promise->fetchResult());
    }

    public function testFetchResultWithNonCalledNestedPromise(): void
    {
        $promiseNested = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');
        $promise = $this->buildPromise(
            onSuccess: function (PromiseInterface $next): void {
            },
            onFail: function (Throwable $error, PromiseInterface $next): void {
            },
            allowNext: true,
        );

        $promise = $promise->next(promise: $promiseNested, autoCall: false);
        $promise->success();
        self::assertNull($promise->fetchResult());

        $promise->fail(new Exception('foo'));
        self::assertNull($promise->fetchResult());
    }

    public function testFetchResultWithNonCalledNestedPromiseWithAutoCall(): void
    {
        $promiseNested = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');
        $promise = $this->buildPromise(
            onSuccess: function (PromiseInterface $next): void {
            },
            onFail: function (Throwable $error, PromiseInterface $next): void {
            },
            allowNext: true,
        );

        $promise = $promise->next(promise: $promiseNested, autoCall: true);
        $promise->success();
        self::assertEquals(expected: 'foo', actual: $promise->fetchResult());

        $promise->fail(new Exception('foo'));
        self::assertEquals(expected: 'bar', actual: $promise->fetchResult());
    }

    public function testFetchResultWithSetDefaultResultOnlyNotCalled(): void
    {
        self::assertEquals(
            expected: 'default',
            actual: $this->buildPromise(
                onSuccess: function (): void {
                },
                onFail: function (): void {
                },
            )->setDefaultResult('default')
                ->fetchResult()
        );
    }

    public function testFetchResultNotCalledWithSetDefaultWithCallableAsResult(): void
    {
        self::assertEquals(
            expected: 'default',
            actual: $this->buildPromise(
                onSuccess: function (): void {
                },
                onFail: function (): void {
                },
            )->setDefaultResult(fn (): string => 'default')
                ->fetchResult()
        );
    }

    public function testFetchResultWithSetDefaultResultCalledWithNullFunction(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);

        $promise->success();
        self::assertNull(
            $promise->setDefaultResult('default')
                ->fetchResult()
        );

        $promise->fail(new Exception('foo'));
        self::assertNull(
            $promise->setDefaultResult('default')
                ->fetchResult()
        );
    }

    public function testFetchResultWithSetDefaultResultCalled(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');

        $promise->success();
        self::assertEquals(
            expected: 'foo',
            actual: $promise->setDefaultResult('default')
                ->fetchResult()
        );

        $promise->fail(new Exception('foo'));
        self::assertEquals(
            expected: 'bar',
            actual: $promise->setDefaultResult('default')
                ->fetchResult()
        );
    }

    public function testFetchResultWithSetDefaultResultAndDefaultNotCalled(): void
    {
        self::assertEquals(
            expected: 'default',
            actual: $this->buildPromise(
                onSuccess: function (): void {
                },
                onFail: function (): void {
                },
            )->setDefaultResult('another')
                ->fetchResult('default')
        );
    }

    public function testFetchResultWithSetDefaultResultAndDefaultCalledWithNullFunction(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);

        $promise->success();
        self::assertNull(
            $promise->setDefaultResult('another')
                ->fetchResult('default')
        );

        $promise->fail(new Exception('foo'));
        self::assertNull(
            $promise->setDefaultResult('another')
                ->fetchResult('default')
        );
    }

    public function testFetchResultWithSetDefaultResultCalledAndDefault(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');

        $promise->success();
        self::assertEquals(
            expected: 'foo',
            actual: $promise->setDefaultResult('another')
                ->fetchResult('default')
        );

        $promise->fail(new Exception('foo'));
        self::assertEquals(
            expected: 'bar',
            actual: $promise->setDefaultResult('anotheer')
                ->fetchResult('default')
        );
    }
    
    public function testFetchResultIfCalledNotCalled(): void
    {
        $this->expectException(RuntimeException::class);
        $this->buildPromise(
            onSuccess: function (): void {
            },
            onFail: function (): void {
            },
        )->fetchResultIfCalled();
    }

    public function testFetchResultIfCalledCalledWithNullFunction(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);

        $promise->success();
        self::assertNull($promise->fetchResultIfCalled());

        $promise->fail(new Exception('foo'));
        self::assertNull($promise->fetchResultIfCalled());
    }

    public function testFetchResultIfCalledCalled(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');

        $promise->success();
        self::assertEquals(expected: 'foo', actual: $promise->fetchResultIfCalled());

        $promise->fail(new Exception('foo'));
        self::assertEquals(expected: 'bar', actual: $promise->fetchResultIfCalled());
    }

    public function testWithSeveralNextWithoutAutoCall(): void
    {
        $pm1 = $this->buildPromise(
            onSuccess: fn (int $value): int => $value * 2,
            onFail: fn (Throwable $error): int => 0,
            allowNext: true,
        );

        $pm1 = $pm1->next(
            promise: $this->buildPromise(
                onSuccess: fn (int $value): int => $value * 3,
                onFail: fn (Throwable $error): int => 1,
                allowNext: true,
            ),
            autoCall: false,
        );

        $pm1 = $pm1->next(
            promise: $this->buildPromise(
                onSuccess: fn (int $value): int => $value - 2,
                onFail: fn (Throwable $error): int => 1,
                allowNext: true,
            ),
            autoCall: false,
        );

        self::assertEquals(
            expected: 6,
            actual: $pm1->success(3)->fetchResult(),
        );
    }

    public function testWithSeveralNextWithAutoCall(): void
    {
        $pm1 = $this->buildPromise(
            onSuccess: fn (int $value): int => $value * 2,
            onFail: fn (Throwable $error): int => 0,
            allowNext: true,
        );

        $pm1 = $pm1->next(
            promise: $this->buildPromise(
                onSuccess: fn (int $value): int => $value * 3,
                onFail: fn (Throwable $error): int => 1,
                allowNext: true,
            ),
            autoCall: true,
        );

        $pm1 = $pm1->next(
            promise: $this->buildPromise(
                onSuccess: fn (int $value): int => $value - 2,
                onFail: fn (Throwable $error): int => 1
            ),
            autoCall: true,
        );

        self::assertEquals(
            expected: 16,
            actual: $pm1->success(3)->fetchResult(),
        );
    }

    public function testInvoke(): void
    {
        $promise = $this->buildPromise(
            onSuccess: fn ($i, $j): float|int|array => $i + $j,
            onFail: fn (Throwable $error): int|string => $error->getCode()
        );

        self::assertEquals(
            expected: 5,
            actual: $promise(2, 3),
        );
    }

    public function testInvokeWithFailCatchable(): void
    {
        $promise = $this->buildPromise(
            onSuccess: fn ($i, $j) => throw new Exception(message: 'foo', code: 204),
            onFail: fn (Throwable $error): int|string => $error->getCode(),
            callOnFailOnException: true
        );

        self::assertEquals(
            expected: 204,
            actual: $promise(2, 3),
        );
    }

    public function testInvokeWithDoubleFailCatchable(): void
    {
        $promise = $this->buildPromise(
            onSuccess: fn ($i, $j) => throw new Exception(message: 'foo', code: 204),
            onFail: fn (Throwable $error) => throw new RuntimeException(message: 'foo', code: 205),
            callOnFailOnException: true
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(205);
        $promise(2, 3);
    }
}
