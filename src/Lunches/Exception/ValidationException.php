<?php

namespace Lunches\Exception;


class ValidationException extends \Exception
{
    public static function invalidSize(array $allowed = [])
    {
        $allowedMsg = 'One of '.implode(', ', $allowed).' allowed.';

        return new static('Invalid size provided.'.$allowedMsg);
    }

    public static function invalidPrice()
    {
        return new static('Price must be positive');
    }
}