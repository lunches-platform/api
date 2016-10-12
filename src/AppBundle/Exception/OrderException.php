<?php

namespace AppBundle\Exception;


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
    /**
     * @param string $msg
     * @return static
     */
    public static function canNotPay($msg)
    {
        return new static('Can not pay for an Order: '.$msg);
    }
}