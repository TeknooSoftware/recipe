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
use Throwable;

/**
 * Regression test about an exception thrown by the success callback of a promise without failure callback but
 * followed by a next promise : the exception must be forwarded to the failure callback of the next promise.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AbstractPromise::class)]
#[CoversClass(Promise::class)]
#[CoversClass(FiberPromise::class)]
final class PromiseNextOnExceptionTest extends TestCase
{
    public function testExceptionOfSuccessWithoutOnFailIsForwardedToTheOnFailOfTheNextPromise(): void
    {
        $log = [];
        $exception = new RuntimeException('success failed');

        $promise = new Promise(
            static function () use ($exception): void {
                throw $exception;
            },
        );

        $promise = $promise->next(
            new Promise(
                static function () use (&$log): string {
                    $log[] = 'next success';

                    return 'next result';
                },
                static function (#[SensitiveParameter] Throwable $error) use (&$log, $exception): string {
                    self::assertSame($exception, $error);
                    $log[] = 'next fail:' . $error->getMessage();

                    return 'fallback';
                },
            ),
        );

        $promise->success('foo');

        self::assertEquals(['next fail:success failed'], $log);
        self::assertEquals('fallback', $promise->fetchResult());
    }

    public function testExceptionOfSuccessWithoutOnFailIsForwardedToTheOnFailOfTheNextPromiseInFiber(): void
    {
        $log = [];
        $exception = new RuntimeException('success failed');

        $promise = new FiberPromise(
            static function () use ($exception): void {
                throw $exception;
            },
        );

        $promise = $promise->next(
            new FiberPromise(
                static function () use (&$log): string {
                    $log[] = 'next success';

                    return 'next result';
                },
                static function (#[SensitiveParameter] Throwable $error) use (&$log, $exception): string {
                    self::assertSame($exception, $error);
                    $log[] = 'next fail:' . $error->getMessage();

                    return 'fallback';
                },
            ),
        );

        $promise->success('foo');

        self::assertEquals(['next fail:success failed'], $log);
        self::assertEquals('fallback', $promise->fetchResult());
    }

    public function testExceptionOfSuccessIsForwardedThroughNextPromisesWithoutOnFail(): void
    {
        $log = [];

        $promise = new Promise(
            static function (): void {
                throw new RuntimeException('success failed');
            },
        );

        $promise = $promise->next(
            new Promise(
                static function () use (&$log): string {
                    $log[] = 'second success';

                    return 'second result';
                },
            ),
        );

        $promise = $promise->next(
            new Promise(
                static function () use (&$log): string {
                    $log[] = 'third success';

                    return 'third result';
                },
                static function (#[SensitiveParameter] Throwable $error) use (&$log): string {
                    $log[] = 'third fail:' . $error->getMessage();

                    return 'fallback';
                },
            ),
        );

        $promise->success('foo');

        self::assertEquals(['third fail:success failed'], $log);
        self::assertEquals('fallback', $promise->fetchResult());
    }

    public function testExceptionThrownByTheOnFailOfTheNextPromiseIsThrown(): void
    {
        $promise = new Promise(
            static function (): void {
                throw new RuntimeException('success failed');
            },
        );

        $promise = $promise->next(
            new Promise(
                null,
                static function (#[SensitiveParameter] Throwable $error): void {
                    throw new LogicException('next fail:' . $error->getMessage(), 0, $error);
                },
            ),
        );

        try {
            $promise->success('foo');
            self::fail('The exception thrown by the failure callback of the next promise must be thrown');
        } catch (LogicException $error) {
            self::assertEquals('next fail:success failed', $error->getMessage());
            self::assertInstanceOf(RuntimeException::class, $error->getPrevious());
        }
    }

    public function testExceptionOfSuccessWithoutOnFailAndWithoutNextPromiseIsStillThrown(): void
    {
        $promise = new Promise(
            static function (): void {
                throw new RuntimeException('success failed');
            },
        );

        try {
            $promise->success('foo');
            self::fail('The exception must be thrown without failure callback and without next promise');
        } catch (RuntimeException $error) {
            self::assertEquals('success failed', $error->getMessage());
        }
    }

    public function testExceptionOfSuccessIsStillThrownWhenOnFailIsNotCalledOnException(): void
    {
        $log = [];

        $promise = new Promise(
            onSuccess: static function (): void {
                throw new RuntimeException('success failed');
            },
            callOnFailOnException: false,
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
        );

        try {
            $promise->success('foo');
            self::fail('The exception must be thrown when the failure callback is not called on exception');
        } catch (RuntimeException $error) {
            self::assertEquals('success failed', $error->getMessage());
        }

        self::assertEquals([], $log);
    }
}
