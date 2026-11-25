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

use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use SensitiveParameter;
use Teknoo\Recipe\Promise\AbstractPromise;
use Teknoo\Recipe\Promise\FiberPromise;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Recipe\Promise\WrappedOneCalledPromise;
use Throwable;

/**
 * Regression test about an exception thrown by the failure callback of a next promise, when this next promise has
 * already received an error (from the success callback of the previous promise) : the exception must be thrown, and
 * not lost by the previous promises without failure callback.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AbstractPromise::class)]
#[CoversClass(Promise::class)]
#[CoversClass(FiberPromise::class)]
#[CoversClass(WrappedOneCalledPromise::class)]
final class PromiseNextFailureThrownTest extends TestCase
{
    public function testExceptionThrownByTheOnFailOfTheNextPromiseFailedBySuccessIsThrown(): void
    {
        $log = [];

        $promise = new Promise(
            static function (string $value, PromiseInterface $next): void {
                $next->fail(new RuntimeException('success failed with ' . $value));
            },
        );

        $promise = $promise->next(
            new Promise(
                static function () use (&$log): void {
                    $log[] = 'next success';
                },
                static function (#[SensitiveParameter] Throwable $error) use (&$log): never {
                    $log[] = 'next fail:' . $error->getMessage();

                    throw new LogicException('next fail:' . $error->getMessage(), 0, $error);
                },
            ),
        );

        $error = null;
        try {
            $promise->success('foo');
        } catch (Throwable $error) {
            //The exception is checked after the call
        }

        self::assertInstanceOf(LogicException::class, $error);
        self::assertEquals('next fail:success failed with foo', $error->getMessage());
        self::assertInstanceOf(RuntimeException::class, $error->getPrevious());

        self::assertEquals(['next fail:success failed with foo'], $log);
    }

    public function testExceptionThrownByTheOnFailOfTheNextPromiseFailedBySuccessIsThrownInFiber(): void
    {
        $log = [];

        $promise = new FiberPromise(
            static function (string $value, PromiseInterface $next): void {
                $next->fail(new RuntimeException('success failed with ' . $value));
            },
        );

        $promise = $promise->next(
            new FiberPromise(
                static function () use (&$log): void {
                    $log[] = 'next success';
                },
                static function (#[SensitiveParameter] Throwable $error) use (&$log): never {
                    $log[] = 'next fail:' . $error->getMessage();

                    throw new LogicException('next fail:' . $error->getMessage(), 0, $error);
                },
            ),
        );

        $error = null;
        try {
            $promise->success('foo');
        } catch (Throwable $error) {
            //The exception is checked after the call
        }

        self::assertInstanceOf(LogicException::class, $error);
        self::assertEquals('next fail:success failed with foo', $error->getMessage());
        self::assertInstanceOf(RuntimeException::class, $error->getPrevious());

        self::assertEquals(['next fail:success failed with foo'], $log);
    }

    public function testExceptionThrownByTheOnFailOfTheLastPromiseIsThrownThroughPromisesWithoutOnFail(): void
    {
        $log = [];
        $exception = new RuntimeException('second failed');

        $promise = new Promise(
            static function (string $value, PromiseInterface $next) use (&$log): void {
                $log[] = 'first success:' . $value;

                $next->success($value . ' bar');
            },
        );

        $promise = $promise->next(
            new Promise(
                static function (string $value, PromiseInterface $next) use (&$log, $exception): void {
                    $log[] = 'second success:' . $value;

                    $next->fail($exception);
                },
            ),
        );

        $promise = $promise->next(
            new Promise(
                static function () use (&$log): void {
                    $log[] = 'third success';
                },
            ),
        );

        $promise = $promise->next(
            new Promise(
                static function () use (&$log): void {
                    $log[] = 'last success';
                },
                static function (#[SensitiveParameter] Throwable $error) use (&$log): never {
                    $log[] = 'last fail:' . $error->getMessage();

                    throw $error;
                },
            ),
        );

        $error = null;
        try {
            $promise->success('foo');
        } catch (Throwable $error) {
            //The exception is checked after the call
        }

        self::assertInstanceOf(RuntimeException::class, $error);
        self::assertSame($exception, $error);

        self::assertEquals(
            [
                'first success:foo',
                'second success:foo bar',
                'last fail:second failed',
            ],
            $log,
        );
    }

    public function testExceptionThrownByTheOnFailOfANotAutoCalledNextPromiseFailedBySuccessIsThrown(): void
    {
        $log = [];

        $promise = new Promise(
            static function (string $value, PromiseInterface $next): void {
                $next->fail(new RuntimeException('success failed with ' . $value));
            },
        );

        $promise = $promise->next(
            new Promise(
                static function () use (&$log): void {
                    $log[] = 'next success';
                },
                static function (#[SensitiveParameter] Throwable $error) use (&$log): never {
                    $log[] = 'next fail:' . $error->getMessage();

                    throw new LogicException('next fail:' . $error->getMessage(), 0, $error);
                },
            ),
            autoCall: false,
        );

        $error = null;
        try {
            $promise->success('foo');
        } catch (Throwable $error) {
            //The exception is checked after the call
        }

        self::assertInstanceOf(LogicException::class, $error);
        self::assertEquals('next fail:success failed with foo', $error->getMessage());
        self::assertInstanceOf(RuntimeException::class, $error->getPrevious());

        self::assertEquals(['next fail:success failed with foo'], $log);
    }

    public function testExceptionOfSuccessWithoutOnFailAndWithANotAutoCalledNextPromiseIsThrown(): void
    {
        $log = [];

        $promise = new Promise(
            static function (): void {
                throw new RuntimeException('success failed');
            },
        );

        $promise = $promise->next(
            new Promise(
                static function () use (&$log): void {
                    $log[] = 'next success';
                },
                static function (#[SensitiveParameter] Throwable $error) use (&$log): void {
                    $log[] = 'next fail:' . $error->getMessage();
                },
            ),
            autoCall: false,
        );

        $error = null;
        try {
            $promise->success('foo');
        } catch (Throwable $error) {
            //The exception is checked after the call
        }

        self::assertInstanceOf(RuntimeException::class, $error);
        self::assertEquals('success failed', $error->getMessage());

        self::assertEquals([], $log);
    }

    public function testExceptionOfSuccessWithoutOnFailIsThrownWhenTheNextPromiseIsAlreadyCalled(): void
    {
        $log = [];

        $promise = new Promise(
            static function (string $value, PromiseInterface $next): void {
                $next->success($value . ' bar');

                throw new RuntimeException('success failed after the next promise');
            },
        );

        $promise = $promise->next(
            new Promise(
                static function (string $value) use (&$log): void {
                    $log[] = 'next success:' . $value;
                },
                static function (#[SensitiveParameter] Throwable $error) use (&$log): void {
                    $log[] = 'next fail:' . $error->getMessage();
                },
            ),
        );

        $error = null;
        try {
            $promise->success('foo');
        } catch (Throwable $error) {
            //The exception is checked after the call
        }

        self::assertInstanceOf(RuntimeException::class, $error);
        self::assertEquals('success failed after the next promise', $error->getMessage());

        self::assertEquals(['next success:foo bar'], $log);
    }

    public function testExceptionThrownByTheOnFailOfTheNextPromiseIsHandledByTheOnFailOfThePromise(): void
    {
        $log = [];

        $promise = new Promise(
            static function (string $value, PromiseInterface $next): void {
                $next->fail(new RuntimeException('success failed with ' . $value));
            },
            static function (#[SensitiveParameter] Throwable $error) use (&$log): void {
                $log[] = 'fail:' . $error->getMessage();
            },
        );

        $promise = $promise->next(
            new Promise(
                static function () use (&$log): void {
                    $log[] = 'next success';
                },
                static function (#[SensitiveParameter] Throwable $error) use (&$log): never {
                    $log[] = 'next fail:' . $error->getMessage();

                    throw $error;
                },
            ),
        );

        $promise->success('foo');

        self::assertEquals(
            [
                'next fail:success failed with foo',
                'fail:success failed with foo',
            ],
            $log,
        );
    }

    public function testExceptionThrownThroughAPromiseWithoutOnFailIsThrownAfterTheOnFailOfThePreviousPromise(): void
    {
        $log = [];
        $exception = new RuntimeException('second failed');

        $promise = new Promise(
            static function (string $value, PromiseInterface $next): void {
                $next->success($value . ' bar');
            },
            static function (#[SensitiveParameter] Throwable $error) use (&$log): void {
                $log[] = 'first fail:' . $error->getMessage();
            },
        );

        $promise = $promise->next(
            new Promise(
                static function (string $value, PromiseInterface $next) use ($exception): void {
                    $next->fail($exception);
                },
            ),
        );

        $promise = $promise->next(
            new Promise(
                static function () use (&$log): void {
                    $log[] = 'last success';
                },
                static function (#[SensitiveParameter] Throwable $error) use (&$log): never {
                    $log[] = 'last fail:' . $error->getMessage();

                    throw $error;
                },
            ),
        );

        $error = null;
        try {
            $promise->success('foo');
        } catch (Throwable $error) {
            //The exception is checked after the call
        }

        self::assertSame($exception, $error);

        self::assertEquals(
            [
                'last fail:second failed',
                'first fail:second failed',
            ],
            $log,
        );
    }

    public function testFailOrThrowOfANotCalledWrapperFailsTheWrappedPromise(): void
    {
        $exception = new RuntimeException('success failed');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('fail')
            ->with($exception)
            ->willReturnSelf();

        $wrapper = new WrappedOneCalledPromise($promise);

        self::assertSame($wrapper, $wrapper->failOrThrow($exception));
    }

    public function testFailOrThrowOfACalledWrapperThrowsTheException(): void
    {
        $exception = new RuntimeException('success failed');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->willReturnSelf();
        $promise->expects($this->never())
            ->method('fail');

        $wrapper = new WrappedOneCalledPromise($promise);
        $wrapper->success('foo');

        $error = null;
        try {
            $wrapper->failOrThrow($exception);
        } catch (Throwable $error) {
            //The exception is checked after the call
        }

        self::assertSame($exception, $error);
    }

    public function testFailOrThrowOfAWrapperWhoseSuccessHasThrownThrowsTheException(): void
    {
        $exception = new RuntimeException('success failed');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->willThrowException($exception);
        $promise->expects($this->never())
            ->method('fail');

        $wrapper = new WrappedOneCalledPromise($promise);

        $error = null;
        try {
            $wrapper->success('foo');
        } catch (Throwable $error) {
            //The exception is checked after the call
        }

        self::assertSame($exception, $error);

        $error = null;
        try {
            $wrapper->failOrThrow($exception);
        } catch (Throwable $error) {
            //The exception is checked after the call
        }

        self::assertSame($exception, $error);
    }

    public function testFailOrThrowOfAResetWrapperFailsTheWrappedPromise(): void
    {
        $exception = new RuntimeException('success failed');

        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->once())
            ->method('success')
            ->willThrowException($exception);
        $promise->expects($this->once())
            ->method('reset')
            ->willReturnSelf();
        $promise->expects($this->once())
            ->method('fail')
            ->with($exception)
            ->willReturnSelf();

        $wrapper = new WrappedOneCalledPromise($promise);

        $error = null;
        try {
            $wrapper->success('foo');
        } catch (Throwable $error) {
            //The exception is checked after the call
        }

        self::assertSame($exception, $error);

        self::assertSame($wrapper, $wrapper->reset());
        self::assertSame($wrapper, $wrapper->failOrThrow($exception));
    }
}
