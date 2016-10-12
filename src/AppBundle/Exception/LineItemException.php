<?php

namespace AppBundle\Exception;


use AppBundle\Entity\Dish;

class LineItemException extends \Exception
{
    /**
     * @param Dish $dish
     * @param \DateTime $today
     * @return static
     */
    public static function notCookingToday(Dish $dish, \DateTime $today = null)
    {
        $date = null !== $today ? $today->format('Y-m-d') : 'specified date';

        return new static("Product #{$dish->getId()} is not cooking for {$date}");
    }
}