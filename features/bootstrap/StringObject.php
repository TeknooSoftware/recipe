<?php

declare(strict_types=1);

use Teknoo\Recipe\ChefInterface;

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

    public static function gotTo(ChefInterface $chef)
    {
        $chef->continue([], 'final');
    }

    /**
     * @inheritDoc
     */
    public function __toString()
    {
        return (string) $this->value;
    }
}
