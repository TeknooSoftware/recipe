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
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\Recipe\Promise;

use Teknoo\Immutable\Exception\ImmutableException;
use Teknoo\Recipe\Promise\PromiseInterface;
use PHPUnit\Framework\TestCase;

/**
 * @copyright   Copyright (c) 2009-2021 EIRL Richard Déloge (richarddeloge@gmail.com)
 * @copyright   Copyright (c) 2020-2021 SASU Teknoo Software (https://teknoo.software)
 *
 * @link        http://teknoo.software/recipe Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
abstract class AbstractPromiseTest extends TestCase
{
    abstract public function buildPromise($onSuccess, $onFail): PromiseInterface;

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
        $this->expectException(ImmutableException::class);
        $this->buildPromise(function () {
        }, function () {
        })
            ->__construct(function () {
            }, function () {
            });
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
        $promise = $this->buildPromise(function () {
        }, function () {
        });
        $nextPromise = $promise->next(null);

        self::assertInstanceOf(PromiseInterface::class, $nextPromise);
        self::assertNotSame($promise, $nextPromise);
    }

    public function testNextSetCallable()
    {
        $promise = $this->buildPromise(function () {
        }, function () {
        });
        $nextPromise = $promise->next($this->createMock(PromiseInterface::class));

        self::assertInstanceOf(PromiseInterface::class, $nextPromise);
        self::assertNotSame($promise, $nextPromise);
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
            }
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
                self::assertIsCallable($next);
                $next($result);
            },
            function () {
                self::fail('Error, fail callback must not be called');
            }
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
                self::assertIsCallable($next);
                $next($result);
            },
            function () {
                self::fail('Error, fail callback must not be called');
            }
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
                self::assertIsCallable($next);
                $next($result);
            }
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
                self::assertIsCallable($next);
                $next($result);
            }
        );

        $promiseWithCallback = $promiseWithCallback->next($promiseWithCallback);

        self::assertInstanceOf(
            PromiseInterface::class,
            $promiseWithCallback->fail(new \Exception('fooBar'))
        );

        self::assertEquals(2, $called, 'Error the success callback must be called');
    }
}
