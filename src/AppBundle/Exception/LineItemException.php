<?php

namespace AppBundle\Exception;


use AppBundle\Entity\Dish;

class LineItemException extends \Exception
{
    /**
     * @param Dish $dish
     * @param \DateTimeImmutable $today
     * @return static
     */
    public static function notCookingToday(Dish $dish, \DateTimeImmutable $today = null)
    {
        $date = null !== $today ? $today->format('Y-m-d') : 'specified date';

        return new static("Dish #{$dish->getId()} is not cooking for {$date}");
    }
}