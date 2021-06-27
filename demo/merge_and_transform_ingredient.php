<?php

declare(strict_types=1);

namespace Acme;

use Teknoo\Recipe\Chef;
use Teknoo\Recipe\ChefInterface;
use Teknoo\Recipe\Ingredient\Attributes\Transform;
use Teknoo\Recipe\Ingredient\Ingredient;
use Teknoo\Recipe\Ingredient\MergeableInterface;
use Teknoo\Recipe\Ingredient\TransformableInterface;
use Teknoo\Recipe\Recipe;

use function var_dump;

require __DIR__ . '/../vendor/autoload.php';

class IntValue implements MergeableInterface, TransformableInterface
{
    public function __construct(
        private int $value = 0,
    ) {
    }

    public function merge(MergeableInterface $mergeable): MergeableInterface
    {
        if ($mergeable instanceof IntValue) {
            $this->value += $mergeable->value;
        }

        return $this;
    }

    public function transform(): mixed
    {
        return $this->value;
    }
}

$recipe = new Recipe();

$recipe = $recipe->require(
    new Ingredient('int', 'initialValue')
);

$recipe = $recipe->cook(
    static function (ChefInterface $chef, int $initialValue): void {
        $chef->continue([IntValue::class => new IntValue($initialValue)]);
    },
    'createBag'
);

$recipe = $recipe->cook(
    static function (ChefInterface $chef, int $initialValue): void {
        $chef->merge(IntValue::class, new IntValue($initialValue + 1));
    },
    'mergeBag'
);

$recipe = $recipe->cook(
    static function (#[Transform(IntValue::class)] int $value): void {
        var_dump($value);
    },
    'transformBag'
);

$chef = new Chef;
$chef->read($recipe);
$chef->process(['initialValue' => 5]);

//Will show "int(11)"
