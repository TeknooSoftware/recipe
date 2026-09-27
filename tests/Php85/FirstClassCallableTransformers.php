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

namespace Teknoo\Tests\Recipe\Php85;

use DateTime;
use PHPUnit\Framework\Assert;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Tests\Recipe\Transformable;

use function strtoupper;

/**
 * Steps using a first-class callable as transformer in the Transform attribute.
 *
 * WARNING : this syntax is a compilation error before PHP 8.5, this file must never be loaded on PHP 8.4 : it is
 * autoloaded only by tests requiring PHP 8.5 (`#[RequiresPhp]`) and by Behat scenarios tagged `@php85` (filtered by
 * the Makefile on PHP 8.4). It is kept outside `tests/Support/` to not be analysed by PHPStan.
 *
 * @copyright   Copyright (c) EIRL Richard Déloge (https://deloge.io - richard@deloge.io)
 * @copyright   Copyright (c) SASU Teknoo Software (https://teknoo.software - contact@teknoo.software)
 * @license     http://teknoo.software/license/bsd-3         3-Clause BSD License
 * @author      Richard Déloge <richard@teknoo.software>
 */
final class FirstClassCallableTransformers
{
    /**
     * @var string[]
     */
    public static array $log = [];

    public static function withStaticMethodTransformer(
        #[Transform(transformer: Transformable::toTransformable(...))] DateTime $date,
    ): void {
        self::$log[] = $date->format('Y-m-d');
    }

    public static function withInternalFunctionTransformer(
        #[Transform(transformer: strtoupper(...))] string $value,
    ): void {
        self::$log[] = $value;
    }

    public static function passDateWithFirstClassCallableTransformer(
        #[Transform(transformer: Transformable::toTransformable(...))] DateTime $transformableDateTime,
        ChefInterface $chef,
    ): void {
        Assert::assertInstanceOf(DateTime::class, $transformableDateTime);

        $chef->updateWorkPlan([DateTime::class => $transformableDateTime]);
    }
}
