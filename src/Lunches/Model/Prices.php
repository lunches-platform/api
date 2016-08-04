<?php

namespace Lunches\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Lunches\Exception\RuntimeException;

/**
 * Class Prices.
 */
class Prices extends ArrayCollection
{
    /**
     * @return array
     */
    public function toArray()
    {
        return array_map(function (Price $price) {
            return $price->toArray();
        }, $this->getValues());
    }

    public function getOrderPrice(Order $order)
    {
        foreach ($this->getIterator() as $priceVariant) {

            $priceItems = PriceFactory::createPriceItemsFromOrder($order, $priceVariant);

            if ($priceVariant->areItemsEquals($priceItems)) {
                return $priceVariant->getValue();
            }
        }

        return $this->sumByProducts($order);
    }

    /**
     * @param LineItem $lineItem
     * @return Price
     * @throws RuntimeException
     */
    public function getLineItemPrice(LineItem $lineItem)
    {
        foreach ($this->getSingleItemPrices() as $price) {
            $priceItem = new PriceItem($price, $lineItem->getProduct(), $lineItem->getSize());

            if ($price->hasPriceItem($priceItem)) {
                return $price;
            }
        }
        throw RuntimeException::priceNotFound($lineItem->getProduct());
    }
    /**
     * Returns prices which has only one PriceItem
     *
     * It is useful to get independent Product price, non in combination of Products
     *
     * @return \Doctrine\Common\Collections\Collection|static
     */
    private function getSingleItemPrices()
    {
        return $this->filter(function (Price $price) {
            return count($price->getItems()) === 1;
        });
    }
    private function sumByProducts(Order $order)
    {
        $price = 0;
        foreach ($order->getLineItems() as $lineItem) {
            $price += $this->getLineItemPrice($lineItem)->getValue();
        }

        return $price;
    }

}
