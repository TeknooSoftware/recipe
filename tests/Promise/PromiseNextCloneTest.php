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
use Teknoo\Recipe\Promise\AbstractPromise;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Teknoo\Recipe\Promise\WrappedOneCalledPromise;

/**
 * Non regression tests : `next()` returns a new promise and never alters the original promise.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AbstractPromise::class)]
#[CoversClass(Promise::class)]
#[CoversClass(WrappedOneCalledPromise::class)]
final class PromiseNextCloneTest extends TestCase
{
    public function testNextReturnsANewPromiseAndKeepsTheOriginalUnchanged(): void
    {
        $log = [];
        $original = new Promise(static fn (string $value): string => $value . '-1');
        $next = new Promise(static function (string $value) use (&$log): void {
            $log[] = 'next:' . $value;
        });

        $chained = $original->next($next);

        $this->assertInstanceOf(PromiseInterface::class, $chained);
        $this->assertNotSame($original, $chained);

        $original->success('a');
        $this->assertEquals([], $log, 'The original promise must not call the next promise');

        $chained->success('b');
        $this->assertEquals(['next:b-1'], $log);
    }

    public function testWrappedPromiseNextReturnsANewWrapperAndKeepsTheOriginalUnchanged(): void
    {
        $log = [];
        $wrapper = new WrappedOneCalledPromise(
            new Promise(static fn (string $value): string => $value . '-1'),
        );
        $next = new Promise(static function (string $value) use (&$log): void {
            $log[] = 'next:' . $value;
        });

        $chained = $wrapper->next($next);

        $this->assertInstanceOf(WrappedOneCalledPromise::class, $chained);
        $this->assertNotSame($wrapper, $chained);

        $wrapper->success('a');
        $this->assertEquals([], $log, 'The original wrapper must not call the next promise');

        $chained->success('b');
        $this->assertEquals(['next:b-1'], $log);
    }
}
