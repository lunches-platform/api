<?php

namespace Lunches\Exception;


class OrderException extends \Exception
{
    /**
     * @param $msg
     * @return static
     */
    public static function failedToChangeStatus($msg)
    {
        return new static($msg);
    }

    /**
     * @param $msg
     * @return static
     */
    public static function updateFailed($msg)
    {
        return new static('Order update failed: '.$msg);
    }
}