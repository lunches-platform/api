<?php

namespace Lunches\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class MenuProducts.
 */
class MenuProducts extends ArrayCollection
{
    /**
     * @return mixed
     */
    public function toArray()
    {
        return array_map(function (MenuProduct $product) {
            return $product->getProduct()->toArray();
        }, parent::toArray());
    }
}
