<?php

namespace Lunches\Exception;

use Lunches\Model\Product;

class RuntimeException extends \Exception
{
    /**
     * @param string|null $propertyName
     * @return static
     */
    public static function requiredPropertyIsEmpty($propertyName = null)
    {
        $propertyName = $propertyName ? ' "'.$propertyName.'"" ' : $propertyName;

        return new static('Internal error. Required property'.$propertyName.'is not initialized');
    }

    /**
     * @param string $objectName
     * @param string $msg
     * @return static
     */
    public static function notFound($objectName, $msg = '')
    {
        return new static($objectName.' not found. '.$msg);
    }

    public static function priceNotFound($type = '')
    {
        $msg = 'Price not found';
        if ($type instanceof \DateTime) {
            $msg .= ' for specified date';
        }
        if ($type instanceof Product) {
            $msg .= ' for product #'.$type->getId();
        }
        return new static($msg);
    }
}