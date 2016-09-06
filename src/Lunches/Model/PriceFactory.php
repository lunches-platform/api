<?php

namespace Lunches\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Lunches\Exception\ValidationException;

class PriceFactory
{
    /** @var ProductRepository  */
    protected $productRepo;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepo = $productRepo;
    }

    public function createFromArray($array)
    {
        // TODO move to separate method
        $required = ['date', 'items', 'price'];
        if (count(array_diff_key(array_flip($required), $array)) > 0) {
            throw ValidationException::requiredEmpty('Invalid price', $required);
        }
        $price = new Price(
            $array['price'],
            $this->createDate($array['date'])
        );
        $price->setItems(
            $this->createItems($price, $array['items'])
        );

        return $price;
    }
    public static function createPriceItemsFromOrder(Order $order, Price $price)
    {
        return new ArrayCollection(array_map(function (LineItem $lineItem) use ($price) {
            return new PriceItem($price, $lineItem->getProduct(), $lineItem->getSize());
        }, $order->getLineItems()->getValues()));
    }

    private function createDate($date)
    {
        try {
            return new \DateTime($date);
        } catch (\Exception $e) {
            throw ValidationException::invalidDate();
        }
    }

    private function createItems(Price $price, array $items)
    {
        $priceItems = [];
        foreach ($items as $key => $item) {
            $priceItems[] = $this->createItem($price, $item);
        }
        if (count($priceItems) === 0) {
            throw ValidationException::invalidPrice('Invalid price provided. Price must have at least one price item');
        }

        return $priceItems;
    }

    private function createItem(Price $price, array $item)
    {
        if (!array_key_exists('size', $item)) {
            throw ValidationException::invalidPrice('Price item must have "size" of product specified');
        }

        // TODO use ProductRepository::get() instead of find() and remove check for product existence as it is not responsibility of this method
        if (!array_key_exists('productId', $item) || !$product = $this->productRepo->find($item['productId'])) {
            throw ValidationException::invalidPrice('Price item must contain valid product');
        }

        return new PriceItem($price, $product, $item['size']);
    }
}