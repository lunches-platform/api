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

    /**
     * @return MenuProducts
     */
    public function sort()
    {
        $menuProducts = $this->getValues();
        usort($menuProducts, function (MenuProduct $a, MenuProduct $b) {
            return $a->getPosition() - $b->getPosition();
        });
        return new static($menuProducts);
    }

    public function hasProduct(Product $product)
    {
        return $this->exists(function ($key, MenuProduct $menuProduct) use ($product) {
            return $menuProduct->sameProduct($product) && null !== $key;
        });
    }
}
