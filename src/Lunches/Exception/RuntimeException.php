<?php

namespace Lunches\Exception;


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
     * @return static
     */
    public static function notFound($objectName)
    {
        return new static($objectName.' not found');
    }
}