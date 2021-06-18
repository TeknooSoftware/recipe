<?php

declare(strict_types=1);

use Teknoo\Recipe\Ingredient\MergeableInterface;

class IntBag implements MergeableInterface
{
    /**
     * @var int
     */
    private $value;

    public function __construct($value)
    {
        $this->value = (int) $value;
    }

    public static function addValue(IntBag $bag, IntBag $toAdd)
    {
        $bag->value += $toAdd->value;
    }

    public static function initializeTo10(IntBag $bag)
    {
        $bag->value = 10;
    }

    public static function increaseValue(IntBag $bag)
    {
        $bag->value++;
    }

    public function merge(MergeableInterface $mergeable): MergeableInterface
    {
        $this->value += $mergeable->value;

        return $this;
    }
}
