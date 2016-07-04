<?php

namespace Lunches\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Products.
 */
class Products extends ArrayCollection
{
    /**
     * @return mixed
     */
    public function toArray()
    {
        return array_map(function (Product $product) {
            return $product->toArray();
        }, parent::toArray());
    }
}
