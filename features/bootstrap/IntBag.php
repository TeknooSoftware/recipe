<?php

class IntBag
{
    /**
     * @var int
     */
    private $value;

    /**
     * IntBag constructor.
     * @param int $value
     */
    public function __construct(int $value)
    {
        $this->value = $value;
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