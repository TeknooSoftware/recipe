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

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Teknoo\Immutable\Exception\ImmutableException;
use Teknoo\Recipe\Promise\Exception\AlreadyCalledPromiseException;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Recipe\Promise\WrappedOneCalledPromise;

/**
 * Regression tests about the wrapper of a promise passed to `next()`.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(WrappedOneCalledPromise::class)]
final class WrappedOneCalledPromiseRegressionTest extends TestCase
{
    public function testConstructorIsImmutable(): void
    {
        $wrapper = new WrappedOneCalledPromise($this->createStub(PromiseInterface::class));

        $this->expectException(ImmutableException::class);
        $wrapper->__construct($this->createStub(PromiseInterface::class));
    }

    public function testCallingFlagIsResetWhenWrappedSuccessThrows(): void
    {
        $promise = $this->createMock(PromiseInterface::class);
        $promise->expects($this->exactly(2))
            ->method('success')
            ->willReturnCallback(
                static function () use (&$promise, &$calls): PromiseInterface {
                    if (1 === ++$calls) {
                        throw new RuntimeException('first call failed');
                    }

                    return $promise;
                }
            );

        $wrapper = new WrappedOneCalledPromise($promise);

        try {
            $wrapper->success('foo');
            $this->fail('The exception of the wrapped promise must be forwarded');
        } catch (RuntimeException $error) {
            $this->assertEquals('first call failed', $error->getMessage());
        }

        //The second call must reach the wrapped promise (not silently ignored)
        $this->assertInstanceOf(PromiseInterface::class, $wrapper->success('bar'));
    }

    public function testSuccessAfterThrowingSuccessIsForwardedAndRejected(): void
    {
        $wrapper = new WrappedOneCalledPromise(
            new Promise(
                static function (): void {
                    throw new RuntimeException('success failed');
                },
            ),
        );

        try {
            $wrapper->success('foo');
            $this->fail('The exception of the wrapped promise must be forwarded');
        } catch (RuntimeException $error) {
            $this->assertEquals('success failed', $error->getMessage());
        }

        //Same behavior than the wrapped promise : it was called, it can not be called again
        $this->expectException(AlreadyCalledPromiseException::class);
        $wrapper->success('bar');
    }
}
