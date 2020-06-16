<?php

class StringObject
{
    /**
     * @var string
     */
    private $value = '';

    /**
     * StringObject constructor.
     * @param string $value
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public static function addTest(self $string)
    {
        $string->value .= ' bar';
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}
