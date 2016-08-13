<?php

namespace Lunches\Exception;


class OrderException extends \Exception
{
    /**
     * @return static
     */
    public static function failedToChangeStatus($msg)
    {
        return new static($msg);
    }

}