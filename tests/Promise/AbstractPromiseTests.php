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
            'fooBar',
            function (): void {
            },
        );
    }

    public function testConstructorBadFailCallable(): void
    {
        $this->expectException(Throwable::class);
        $this->buildPromise(
            function (): void {
            },
            'fooBar',
        );
    }

    public function testConstructor(): void
    {
        self::assertInstanceOf(
            PromiseInterface::class,
            $this->buildPromise(
                function (): void {
                },
                function (): void {
                },
            ),
        );
    }

    public function testConstructorAtNull(): void
    {
        self::assertInstanceOf(
            PromiseInterface::class,
            $this->buildPromise(null, null)
        );
    }

    public function testConstructorImmutable(): void
    {
        $this->expectException(Error::class);
        $this->buildPromise(
            function (): void {
            },
            function (): void {
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
            function (): void {
            },
            function (): void {
            },
        )->next('fooBar');
    }

    public function testNextSetNull(): void
    {
        $promise = $this->buildPromise(
            function (): void {
            },
            function (): void {
            },
            true,
        );
        $nextPromise = $promise->next(null);

        self::assertInstanceOf(PromiseInterface::class, $nextPromise);
        self::assertNotSame($promise, $nextPromise);
    }

    public function testNextSetCallable(): void
    {
        $promise = $this->buildPromise(
            function (): void {
            },
            function (): void {
            },
            true,
        );
        $nextPromise = $promise->next($this->createMock(PromiseInterface::class));

        self::assertInstanceOf(PromiseInterface::class, $nextPromise);
        self::assertNotSame($promise, $nextPromise);
    }

    public function testNextSetCallableNotAllowed(): void
    {
        $promise = $this->buildPromise(
            function (): void {
            },
            function (): void {
            },
            false
        );

        $this->expectException(RuntimeException::class);
        $promise->next($this->createMock(PromiseInterface::class));
    }

    public function testSuccess(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function ($result) use (&$called): void {
                $called = true;
                self::assertEquals('foo', $result);
            },
            function (): void {
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
            function (): void {
                self::fail('Error, fail callback must not be called');
            }
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithoutSuccessCallback->success('foo')
        );
    }

    public function testSuccessWithNextNotDefined(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function ($result, $next) use (&$called): void {
                $called = true;
                self::assertEquals('foo', $result);
                self::assertInstanceOf(
                    PromiseInterface::class,
                    $next
                );
                $next->success($result);
            },
            function (): void {
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

    public function testSuccessWithNextNotAllowed(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function ($result, $next = null) use (&$called): void {
                $called = true;
                self::assertEquals('foo', $result);
                self::assertNull($next);
            },
            function (): void {
                self::fail('Error, fail callback must not be called');
            },
            false,
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->success('foo')
        );

        self::assertTrue($called, 'Error the success callback must be called');
    }

    public function testSuccessWithNexDefined(): void
    {
        $called = 0;
        $promiseWithCallback = $this->buildPromise(
            function ($result, $next) use (&$called): void {
                ++$called;
                self::assertEquals('foo', $result);
                self::assertInstanceOf(
                    PromiseInterface::class,
                    $next
                );
                $next->success($result);
            },
            function (): void {
                self::fail('Error, fail callback must not be called');
            },
            true,
        );

        $promiseWithCallback = $promiseWithCallback->next($promiseWithCallback);

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->success('foo')
        );

        self::assertEquals(2, $called, 'Error the success callback must be called');
    }

    public function testFail(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function (): void {
                self::fail('Error, success callback must not be called');
            },
            function ($result) use (&$called): void {
                $called = true;
                self::assertEquals(new Exception('fooBar'), $result);
            },
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->fail(new Exception('fooBar'))
        );

        self::assertTrue($called, 'Error the success callback must be called');

        $promiseWithoutSuccessCallback = $this->buildPromise(
            function (): void {
                self::fail('Error, success callback must not be called');
            },
            null
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithoutSuccessCallback->fail(new Exception('fooBar'))
        );
    }

    public function testFailWithNextNotDefined(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function (): void {
                self::fail('Error, success callback must not be called');
            },
            function ($result, $next) use (&$called): void {
                $called = true;
                self::assertEquals(new Exception('fooBar'), $result);
                self::assertInstanceOf(
                    PromiseInterface::class,
                    $next
                );
                $next->fail($result);
            },
            true,
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->fail(new Exception('fooBar'))
        );

        self::assertTrue($called, 'Error the success callback must be called');
    }

    public function testFailWithNextNotAllowed(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            function (): void {
                self::fail('Error, success callback must not be called');
            },
            function ($result, $next = null) use (&$called): void {
                $called = true;
                self::assertEquals(new Exception('fooBar'), $result);
                self::assertNull($next);
            },
            false,
        );

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->fail(new Exception('fooBar'))
        );

        self::assertTrue($called, 'Error the success callback must be called');
    }

    public function testFailWithNextDefined(): void
    {
        $called = 0;
        $promiseWithCallback = $this->buildPromise(
            function (): void {
                self::fail('Error, success callback must not be called');
            },
            function ($result, $next) use (&$called): void {
                ++$called;
                self::assertEquals(new Exception('fooBar'), $result);
                self::assertInstanceOf(
                    PromiseInterface::class,
                    $next
                );
                $next->fail($result);
            },
            true,
        );

        $promiseWithCallback = $promiseWithCallback->next($promiseWithCallback);

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->fail(new Exception('fooBar'))
        );

        self::assertEquals(2, $called, 'Error the success callback must be called');
    }

    public function testSetDefaultResult(): void
    {
        self::assertInstanceOf(
            PromiseInterface::class,
            $this->buildPromise(
                function (): void {
                },
                function (): void {
                }
            )->setDefaultResult('foo')
        );
    }

    public function testFetchResultNotCalled(): void
    {
        self::assertEquals(
            'default',
            $this->buildPromise(
                function (): void {
                },
                function (): void {
                },
            )->fetchResult('default')
        );
    }

    public function testFetchResultNotCalledWithCallableAsResult(): void
    {
        self::assertEquals(
            'default',
            $this->buildPromise(
                function (): void {
                },
                function (): void {
                },
            )->fetchResult(fn (): string => 'default')
        );
    }

    public function testFetchResultCalledWithNullFunction(): void
    {
        $promise = $this->buildPromise(null, null);

        $promise->success();
        self::assertNull($promise->fetchResult('default'));

        $promise->fail(new Exception('foo'));
        self::assertNull($promise->fetchResult('default'));
    }

    public function testFetchResultCalled(): void
    {
        $promise = $this->buildPromise(fn (): string => 'foo', fn (): string => 'bar');

        $promise->success();
        self::assertEquals('foo', $promise->fetchResult('default'));

        $promise->fail(new Exception('foo'));
        self::assertEquals('bar', $promise->fetchResult('default'));
    }

    public function testFetchResultWithNestedPromise(): void
    {
        $promiseNested = $this->buildPromise(fn (): string => 'foo', fn (): string => 'bar');
        $promise = $this->buildPromise(
            function (PromiseInterface $next): void {
                $next->success('foo');
            },
            function (Throwable $error, PromiseInterface $next): void {
                $next->fail($error);
            },
            true
        );

        $promise = $promise->next($promiseNested);
        $promise->success();
        self::assertEquals('foo', $promise->fetchResult());

        $promise->fail(new Exception('foo'));
        self::assertEquals('bar', $promise->fetchResult());
    }

    public function testFetchResultWithNonCalledNestedPromise(): void
    {
        $promiseNested = $this->buildPromise(fn (): string => 'foo', fn (): string => 'bar');
        $promise = $this->buildPromise(
            function (PromiseInterface $next): void {
            },
            function (Throwable $error, PromiseInterface $next): void {
            },
            true,
        );

        $promise = $promise->next($promiseNested);
        $promise->success();
        self::assertNull($promise->fetchResult());

        $promise->fail(new Exception('foo'));
        self::assertNull($promise->fetchResult());
    }

    public function testFetchResultWithNonCalledNestedPromiseWithAutoCall(): void
    {
        $promiseNested = $this->buildPromise(fn (): string => 'foo', fn (): string => 'bar');
        $promise = $this->buildPromise(
            function (PromiseInterface $next): void {
            },
            function (Throwable $error, PromiseInterface $next): void {
            },
            true,
        );

        $promise = $promise->next($promiseNested, true);
        $promise->success();
        self::assertEquals('foo', $promise->fetchResult());

        $promise->fail(new Exception('foo'));
        self::assertEquals('bar', $promise->fetchResult());
    }

    public function testFetchResultWithSetDefaultResultOnlyNotCalled(): void
    {
        self::assertEquals(
            'default',
            $this->buildPromise(
                function (): void {
                },
                function (): void {
                },
            )->setDefaultResult('default')
                ->fetchResult()
        );
    }

    public function testFetchResultNotCalledWithSetDefaultWithCallableAsResult(): void
    {
        self::assertEquals(
            'default',
            $this->buildPromise(
                function (): void {
                },
                function (): void {
                },
            )->setDefaultResult(fn (): string => 'default')
                ->fetchResult()
        );
    }

    public function testFetchResultWithSetDefaultResultCalledWithNullFunction(): void
    {
        $promise = $this->buildPromise(null, null);

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
        $promise = $this->buildPromise(fn (): string => 'foo', fn (): string => 'bar');

        $promise->success();
        self::assertEquals(
            'foo',
            $promise->setDefaultResult('default')
                ->fetchResult()
        );

        $promise->fail(new Exception('foo'));
        self::assertEquals(
            'bar',
            $promise->setDefaultResult('default')
                ->fetchResult()
        );
    }

    public function testFetchResultWithSetDefaultResultAndDefaultNotCalled(): void
    {
        self::assertEquals(
            'default',
            $this->buildPromise(
                function (): void {
                },
                function (): void {
                },
            )->setDefaultResult('another')
                ->fetchResult('default')
        );
    }

    public function testFetchResultWithSetDefaultResultAndDefaultCalledWithNullFunction(): void
    {
        $promise = $this->buildPromise(null, null);

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
        $promise = $this->buildPromise(fn (): string => 'foo', fn (): string => 'bar');

        $promise->success();
        self::assertEquals(
            'foo',
            $promise->setDefaultResult('another')
                ->fetchResult('default')
        );

        $promise->fail(new Exception('foo'));
        self::assertEquals(
            'bar',
            $promise->setDefaultResult('anotheer')
                ->fetchResult('default')
        );
    }
    
    public function testFetchResultIfCalledNotCalled(): void
    {
        $this->expectException(RuntimeException::class);
        $this->buildPromise(
            function (): void {
            }, function (): void {
            },
        )->fetchResultIfCalled();
    }

    public function testFetchResultIfCalledCalledWithNullFunction(): void
    {
        $promise = $this->buildPromise(null, null);

        $promise->success();
        self::assertNull($promise->fetchResultIfCalled());

        $promise->fail(new Exception('foo'));
        self::assertNull($promise->fetchResultIfCalled());
    }

    public function testFetchResultIfCalledCalled(): void
    {
        $promise = $this->buildPromise(fn (): string => 'foo', fn (): string => 'bar');

        $promise->success();
        self::assertEquals('foo', $promise->fetchResultIfCalled());

        $promise->fail(new Exception('foo'));
        self::assertEquals('bar', $promise->fetchResultIfCalled());
    }

    public function testWithSeveralNextWithoutAutoCall(): void
    {
        $pm1 = $this->buildPromise(
            fn (int $value): int => $value * 2,
            fn (Throwable $error): int => 0,
            true,
        );

        $pm1 = $pm1->next(
            $this->buildPromise(
                fn (int $value): int => $value * 3,
                fn (Throwable $error): int => 1,
                true,
            )
        );

        $pm1 = $pm1->next(
            $this->buildPromise(
                fn (int $value): int => $value - 2,
                fn (Throwable $error): int => 1,
                true,
            )
        );

        self::assertEquals(
            6,
            $pm1->success(3)->fetchResult(),
        );
    }

    public function testWithSeveralNextWithAutoCall(): void
    {
        $pm1 = $this->buildPromise(
            fn (int $value): int => $value * 2,
            fn (Throwable $error): int => 0,
            true,
        );

        $pm1 = $pm1->next(
            promise: $this->buildPromise(
                fn (int $value): int => $value * 3,
                fn (Throwable $error): int => 1,
                true,
            ),
            autoCall: true,
        );

        $pm1 = $pm1->next(
            promise: $this->buildPromise(
                fn (int $value): int => $value - 2,
                fn (Throwable $error): int => 1
            ),
            autoCall: true,
        );

        self::assertEquals(
            16,
            $pm1->success(3)->fetchResult(),
        );
    }

    public function testInvoke(): void
    {
        $promise = $this->buildPromise(
            fn ($i, $j): float|int|array => $i + $j,
            fn (Throwable $error): int|string => $error->getCode()
        );

        self::assertEquals(
            5,
            $promise(2, 3),
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
            204,
            $promise(2, 3),
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
