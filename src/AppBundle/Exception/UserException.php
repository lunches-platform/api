<?php

namespace AppBundle\Exception;


class UserException extends \Exception
{
    /**
     * @return static
     */
    public static function insufficientFunds()
    {
        return new static('Insufficient funds to charge');
    }
}