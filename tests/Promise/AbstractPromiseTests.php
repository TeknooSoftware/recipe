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

use Error;
use Exception;
use RuntimeException;
use Teknoo\Immutable\Exception\ImmutableException;
use Teknoo\Recipe\Promise\Exception\AlreadyCalledPromiseException;
use Teknoo\Recipe\Promise\PromiseInterface;
use PHPUnit\Framework\TestCase;
use Throwable;

/**
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
abstract class AbstractPromiseTests extends TestCase
{
    abstract public function buildPromise(
        ?callable $onSuccess,
        ?callable $onFail,
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
        $this->assertInstanceOf(
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
        $this->assertInstanceOf(
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

        $this->assertInstanceOf(expected: PromiseInterface::class, actual: $nextPromise);
        $this->assertNotSame(expected: $promise, actual: $nextPromise);
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
        $nextPromise = $promise->next($this->createStub(PromiseInterface::class));

        $this->assertInstanceOf(expected: PromiseInterface::class, actual: $nextPromise);
        $this->assertNotSame(expected: $promise, actual: $nextPromise);
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
        $promise->next($this->createStub(PromiseInterface::class));
    }

    public function testSuccess(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            onSuccess: function ($result) use (&$called): void {
                $called = true;
                $this->assertEquals(expected: 'foo', actual: $result);
            },
            onFail: function (): void {
                self::fail('Error, fail callback must not be called');
            },
            allowNext: true
        );

        $this->assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->success('foo')
        );

        $this->assertTrue(condition: $called, message: 'Error the success callback must be called');

        $promiseWithoutSuccessCallback = $this->buildPromise(
            onSuccess: null,
            onFail: function (): void {
                self::fail('Error, fail callback must not be called');
            }
        );

        $this->assertInstanceOf(
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
                $this->assertEquals(expected: 'foo', actual: $result);
                $this->assertInstanceOf(
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

        $this->assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->success('foo')
        );

        $this->assertTrue(condition: $called, message: 'Error the success callback must be called');
    }

    public function testSuccessWithNextNotAllowed(): void
    {
        $called = false;
        $promiseWithCallback = $this->buildPromise(
            onSuccess: function ($result, $next = null) use (&$called): void {
                $called = true;
                $this->assertEquals(expected: 'foo', actual: $result);
                $this->assertNull($next);
            },
            onFail: function (): void {
                self::fail('Error, fail callback must not be called');
            },
            allowNext: false,
        );

        $this->assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->success('foo')
        );

        $this->assertTrue(condition: $called, message: 'Error the success callback must be called');
    }

    public function testSuccessWithNexDefined(): void
    {
        $called = 0;
        $promiseWithCallback = $this->buildPromise(
            onSuccess: function ($result, $next) use (&$called): void {
                ++$called;
                $this->assertEquals(expected: 'foo', actual: $result);
                $this->assertInstanceOf(
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

        $this->assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->success('foo')
        );

        $this->assertEquals(expected: 2, actual: $called, message: 'Error the success callback must be called');
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
                $this->assertEquals(expected: new Exception('fooBar'), actual: $result);
            },
        );

        $this->assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->fail(new Exception('fooBar'))
        );

        $this->assertTrue(condition: $called, message: 'Error the success callback must be called');

        $promiseWithoutSuccessCallback = $this->buildPromise(
            onSuccess: function (): void {
                self::fail('Error, success callback must not be called');
            },
            onFail: null
        );

        $this->assertInstanceOf(
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
                $this->assertEquals(expected: new Exception('fooBar'), actual: $result);
                $this->assertInstanceOf(
                    expected: PromiseInterface::class,
                    actual: $next
                );
                $next->fail($result);
            },
            allowNext: true,
        );

        $this->assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->fail(new Exception('fooBar'))
        );

        $this->assertTrue(condition: $called, message: 'Error the success callback must be called');
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
                $this->assertEquals(expected: new Exception('fooBar'), actual: $result);
                $this->assertNull($next);
            },
            allowNext: false,
        );

        $this->assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->fail(new Exception('fooBar'))
        );

        $this->assertTrue(condition: $called, message: 'Error the success callback must be called');
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
                $this->assertEquals(expected: new Exception('fooBar'), actual: $result);
                $this->assertInstanceOf(
                    expected: PromiseInterface::class,
                    actual: $next
                );
                $next->fail($result);
            },
            allowNext: true,
        );

        $promiseWithCallback = $promiseWithCallback->next($promiseWithCallback);

        $this->assertInstanceOf(
            expected: PromiseInterface::class,
            actual: $promiseWithCallback->fail(new Exception('fooBar'))
        );

        $this->assertEquals(expected: 2, actual: $called, message: 'Error the success callback must be called');
    }

    public function testExceptionWhenFailIsCalledAfterSuccess(): void
    {
        $promise = $this->buildPromise(
            onSuccess: fn (): bool => true,
            onFail: fn (): bool => false,
        );

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->success(),
        );

        $this->assertTrue($promise->fetchResult());

        $this->expectException(AlreadyCalledPromiseException::class);
        $promise->fail(new Exception('foo'));
    }

    public function testExceptionWhenSuccessIsCalledAfterFail(): void
    {
        $promise = $this->buildPromise(
            onSuccess: fn (): bool => true,
            onFail: fn (): bool => false,
        );

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->fail(new Exception('foo')),
        );

        $this->assertFalse($promise->fetchResult());

        $this->expectException(AlreadyCalledPromiseException::class);
        $promise->success();
    }

    public function testExceptionWhenFailIsRecalledInTryCatchWithSameException(): void
    {
        $promise = $this->buildPromise(
            onSuccess: fn (): bool => true,
            onFail: fn (Throwable $a): never => throw new RuntimeException(message: 'foo', previous: $a),
        );

        $exception = new Exception('foo');
        $e = null;
        try {
            try {
                $promise->fail($exception);
            } catch (Throwable $e) {
                $promise->fail($e);
            }
        } catch (Throwable $error) {
            $e = $error;
        }

        $this->assertSame($exception, $e->getPrevious());
    }

    public function testExceptionWhenSuccessIsCalledAfterFailAndProhibitReuse(): void
    {
        $promise = $this->buildPromise(
            onSuccess: fn (): bool => true,
            onFail: fn (): bool => false,
        );

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->allowReuse(),
        );

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->fail(new Exception('foo')),
        );

        $this->assertFalse($promise->fetchResult());

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->prohibitReuse(),
        );

        $this->expectException(AlreadyCalledPromiseException::class);
        $promise->success();
    }

    public function testNoExceptionWhenFailIsCalledAfterSuccessAndReset(): void
    {
        $promise = $this->buildPromise(
            onSuccess: fn (): bool => true,
            onFail: fn (): bool => false,
        );

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->success(),
        );

        $this->assertTrue($promise->fetchResult());

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->reset(),
        );

        $promise->fail(new Exception('foo'));
        $this->assertFalse($promise->fetchResult());
    }

    public function testNoExceptionWhenFailIsCalledAfterSuccessAndAllowReuse(): void
    {
        $promise = $this->buildPromise(
            onSuccess: fn (): bool => true,
            onFail: fn (): bool => false,
        );

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->allowReuse(),
        );

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->success(),
        );

        $this->assertTrue($promise->fetchResult());

        $promise->fail(new Exception('foo'));
        $this->assertFalse($promise->fetchResult());
    }

    public function testNoExceptionWhenSuccessIsCalledAfterFailAndReset(): void
    {
        $promise = $this->buildPromise(
            onSuccess: fn (): bool => true,
            onFail: fn (): bool => false,
        );

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->fail(new Exception('foo')),
        );

        $this->assertFalse($promise->fetchResult());

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->reset(),
        );

        $promise->success();
        $this->assertTrue($promise->fetchResult());
    }

    public function testNoExceptionWhenSuccessIsCalledAfterFailAndAllowResue(): void
    {
        $promise = $this->buildPromise(
            onSuccess: fn (): bool => true,
            onFail: fn (): bool => false,
        );

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->allowReuse(),
        );

        $this->assertInstanceOf(
            PromiseInterface::class,
            $promise->fail(new Exception('foo')),
        );

        $this->assertFalse($promise->fetchResult());

        $promise->success();
        $this->assertTrue($promise->fetchResult());
    }

    public function testSetDefaultResult(): void
    {
        $this->assertInstanceOf(
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
        $this->assertEquals(
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
        $this->assertEquals(
            expected: 'default',
            actual: $this->buildPromise(
                onSuccess: function (): void {
                },
                onFail: function (): void {
                },
            )->fetchResult(fn (): string => 'default')
        );
    }

    public function testFetchResultCalledWithNullFunctionAfterSuccess(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);
        $promise->success();
        $this->assertNull($promise->fetchResult('default'));
    }

    public function testFetchResultCalledWithNullFunctionAfterFail(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);
        $promise->fail(new Exception('foo'));
        $this->assertNull($promise->fetchResult('default'));
    }

    public function testFetchResultCalledAfterSuccess(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');
        $promise->success();
        $this->assertEquals(expected: 'foo', actual: $promise->fetchResult('default'));
    }

    public function testFetchResultCalledAfterFail(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');
        $promise->fail(new Exception('foo'));
        $this->assertEquals(expected: 'bar', actual: $promise->fetchResult('default'));
    }

    public function testFetchResultWithNestedPromiseAutoCalledAfterSuccess(): void
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
        $this->assertEquals(expected: 'foo', actual: $promise->fetchResult());
    }

    public function testFetchResultWithNestedPromiseAutoCalledAfterFail(): void
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
        $promise->fail(new Exception('foo'));
        $this->assertEquals(expected: 'bar', actual: $promise->fetchResult());
    }

    public function testFetchResultWithNonCalledNestedPromiseAfterSuccess(): void
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
        $this->assertNull($promise->fetchResult());
    }

    public function testFetchResultWithNonCalledNestedPromiseAfterFail(): void
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

        $promise->fail(new Exception('foo'));
        $this->assertNull($promise->fetchResult());
    }

    public function testFetchResultAfterSuccessWithNonCalledNestedPromiseWithAutoCallAfterSuccess(): void
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
        $this->assertEquals(expected: 'foo', actual: $promise->fetchResult());
    }

    public function testFetchResultAfterSuccessWithNonCalledNestedPromiseWithAutoCallAfterFail(): void
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
        $promise->fail(new Exception('foo'));
        $this->assertEquals(expected: 'bar', actual: $promise->fetchResult());
    }

    public function testFetchResultWithSetDefaultResultOnlyNotCalled(): void
    {
        $this->assertEquals(
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
        $this->assertEquals(
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

    public function testFetchResultWithSetDefaultResultCalledWithNullFunctionAfterSuccess(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);

        $promise->success();
        $this->assertNull(
            $promise->setDefaultResult('default')
                ->fetchResult()
        );
    }

    public function testFetchResultWithSetDefaultResultCalledWithNullFunctionAfterFail(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);

        $promise->success();
        $this->assertNull(
            $promise->setDefaultResult('default')
                ->fetchResult()
        );
    }

    public function testFetchResultWithSetDefaultResultCalledAfterSuccess(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');

        $promise->success();
        $this->assertEquals(
            expected: 'foo',
            actual: $promise->setDefaultResult('default')
                ->fetchResult()
        );
    }

    public function testFetchResultWithSetDefaultResultCalledAfterFail(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');

        $promise->fail(new Exception('foo'));
        $this->assertEquals(
            expected: 'bar',
            actual: $promise->setDefaultResult('default')
                ->fetchResult()
        );
    }

    public function testFetchResultWithSetDefaultResultAndDefaultNotCalled(): void
    {
        $this->assertEquals(
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

    public function testFetchResultWithSetDefaultResultAndDefaultCalledWithNullFunctionAfterSuccess(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);

        $promise->success();
        $this->assertNull(
            $promise->setDefaultResult('another')
                ->fetchResult('default')
        );
    }

    public function testFetchResultWithSetDefaultResultAndDefaultCalledWithNullFunctionAfterFail(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);

        $promise->fail(new Exception('foo'));
        $this->assertNull(
            $promise->setDefaultResult('another')
                ->fetchResult('default')
        );
    }

    public function testFetchResultWithSetDefaultResultCalledAndDefaultAfterSuccess(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');

        $promise->success();
        $this->assertEquals(
            expected: 'foo',
            actual: $promise->setDefaultResult('another')
                ->fetchResult('default')
        );
    }

    public function testFetchResultWithSetDefaultResultCalledAndDefaultAfterFail(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');

        $promise->fail(new Exception('foo'));
        $this->assertEquals(
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

    public function testFetchResultIfCalledCalledWithNullFunctionAfterSuccess(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);

        $promise->success();
        $this->assertNull($promise->fetchResultIfCalled());
    }

    public function testFetchResultIfCalledCalledWithNullFunctionAfterFail(): void
    {
        $promise = $this->buildPromise(onSuccess: null, onFail: null);

        $promise->fail(new Exception('foo'));
        $this->assertNull($promise->fetchResultIfCalled());
    }

    public function testFetchResultIfCalledCalledAfterSuccess(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');

        $promise->success();
        $this->assertEquals(expected: 'foo', actual: $promise->fetchResultIfCalled());
    }

    public function testFetchResultIfCalledCalledAfterFail(): void
    {
        $promise = $this->buildPromise(onSuccess: fn (): string => 'foo', onFail: fn (): string => 'bar');

        $promise->fail(new Exception('foo'));
        $this->assertEquals(expected: 'bar', actual: $promise->fetchResultIfCalled());
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

        $this->assertEquals(
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

        $this->assertEquals(
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

        $this->assertEquals(
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

        $this->assertEquals(
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
