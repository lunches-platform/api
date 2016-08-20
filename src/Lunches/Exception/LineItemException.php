<?php

namespace Lunches\Exception;


use Lunches\Model\Product;

class LineItemException extends \Exception
{
    /**
     * @param Product $product
     * @param \DateTime $today
     * @return static
     */
    public static function notCookingToday(Product $product, \DateTime $today = null)
    {
        $date = null !== $today ? $today->format('Y-m-d') : 'specified date';

        return new static("Product #{$product->getId()} is not cooking for {$date}");
    }
}