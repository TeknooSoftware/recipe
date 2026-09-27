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
use RuntimeException;
use Teknoo\Recipe\Promise\AbstractPromise;
use Teknoo\Recipe\Promise\Promise;
use Teknoo\Recipe\Promise\PromiseInterface;
use Throwable;

/**
 * Regression test about the failing flag of a promise when its onFail callback throws.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
#[CoversClass(AbstractPromise::class)]
#[CoversClass(Promise::class)]
final class PromiseFailingFlagTest extends TestCase
{
    public function testOnFailIsCalledForASuccessExceptionAfterAThrowingOnFail(): void
    {
        $log = [];
        $failCalls = 0;
        $promise = new Promise(
            static function (): void {
                throw new RuntimeException('success failed');
            },
            static function (Throwable $error) use (&$log, &$failCalls): void {
                if (1 === ++$failCalls) {
                    throw new RuntimeException('fail failed');
                }

                $log[] = 'fail:' . $error->getMessage();
            },
        );
        $promise->allowReuse();

        try {
            $promise->fail(new Exception('first'));
            $this->fail('The exception of onFail must be forwarded');
        } catch (RuntimeException $error) {
            $this->assertEquals('fail failed', $error->getMessage());
        }

        //The exception thrown by onSuccess must be forwarded to onFail, the promise is no longer failing
        $this->assertInstanceOf(PromiseInterface::class, $promise->success('foo'));
        $this->assertEquals(['fail:success failed'], $log);
    }
}
