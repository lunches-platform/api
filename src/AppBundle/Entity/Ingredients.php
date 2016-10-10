<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Ingredients.
 */
class Ingredients extends ArrayCollection
{
    /**
     * @return mixed
     */
    public function toArray()
    {
        return array_map(function (Ingredient $ingredient) {
            return $ingredient->toArray();
        }, parent::toArray());
    }
}
