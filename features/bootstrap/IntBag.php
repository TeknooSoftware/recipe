<?php

declare(strict_types=1);

class IntBag
{
    /**
     * @var int
     */
    private $value;

    public function __construct($value)
    {
        $this->value = (int) $value;
    }

    public static function initializeTo10(IntBag $bag)
    {
        $bag->value = 10;
    }

    public static function increaseValue(IntBag $bag)
    {
        $bag->value++;
    }
}
