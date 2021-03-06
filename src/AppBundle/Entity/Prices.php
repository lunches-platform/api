<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Exception\RuntimeException;
use Underscore\Types\Arrays;

/**
 * Class Prices.
 */
class Prices extends ArrayCollection
{
    /**
     * @param bool $groupByDate
     * @return array
     */
    public function toArray($groupByDate = false)
    {
        $prices = array_map(function (Price $price) {
            return $price->toArray();
        }, $this->getValues());

        if ($groupByDate === true) {
            $prices = Arrays::group($prices, function($price) {
                return $price['date'];
            });
        }

        return $prices;
    }

    public function getOrderPrice(Order $order)
    {
        foreach ($this->getIterator() as $priceVariant) {

            /** @var Price $priceVariant */
            if ($priceVariant->getDate() != $order->getShipmentDate()) {
                continue;
            }
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
            $priceItem = new PriceItem($price, $lineItem->getDish(), $lineItem->getSize());

            if ($price->hasPriceItem($priceItem)) {
                return $price;
            }
        }
        throw RuntimeException::priceNotFound($lineItem->getDish());
    }
    /**
     * Returns prices which has only one PriceItem
     *
     * It is useful to get independent Dish price, non in combination of Dishes
     *
     * @return \Doctrine\Common\Collections\Collection|static
     */
    public function getSingleItemPrices()
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
