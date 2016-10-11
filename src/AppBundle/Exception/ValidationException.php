<?php

namespace AppBundle\Exception;


class ValidationException extends \Exception
{
    public static function invalidSize(array $allowed = [])
    {
        $allowedMsg = count($allowed) ? 'One of '.implode(', ', $allowed).' allowed.' : '';

        return new static('Invalid size provided.'.$allowedMsg);
    }

    public static function invalidPrice($msg = '')
    {
        return new static('Invalid price provided. '.$msg);
    }

    public static function invalidDate($msg = '')
    {
        return new static('Invalid date provided. '.$msg);
    }

    public static function invalidOrder($msg)
    {
        return new static('Invalid order. '.$msg);
    }

    public static function requiredEmpty($msg, array $required)
    {
        return new static($msg.'. There are no required fields. Required are '.implode(', ', $required));
    }

    public static function invalidLineItem($msg)
    {
        return new static('Invalid LineItem. '.$msg);
    }

    public static function invalidUser($msg)
    {
        return new static('Invalid User. '.$msg);
    }

    public static function invalidTransaction($msg)
    {
        return new static('Invalid Transaction. '.$msg);
    }
}