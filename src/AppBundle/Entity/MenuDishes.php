<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class MenuDishes.
 */
class MenuDishes extends ArrayCollection implements \JsonSerializable
{
    public function jsonSerialize()
    {
        return array_map(function (MenuDish $menuDish) {
            return $menuDish->getDish();
        }, parent::toArray());
    }
    /**
     * @return MenuDishes
     */
    public function sort()
    {
        $menuDishes = $this->getValues();
        usort($menuDishes, function (MenuDish $a, MenuDish $b) {
            return $a->getPosition() - $b->getPosition();
        });
        return new static($menuDishes);
    }

    public function hasDish(Dish $dish)
    {
        return $this->exists(function ($key, MenuDish $menuDish) use ($dish) {
            return $menuDish->sameDish($dish) && null !== $key;
        });
    }
}
