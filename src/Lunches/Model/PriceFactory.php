<?php

namespace Lunches\Model;

use Lunches\Exception\ValidationException;
use Doctrine\ORM\EntityManager;

class PriceFactory
{
    /** @var ProductRepository  */
    protected $productRepo;
    
    /** @var  EntityManager */
    protected $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
        $this->productRepo = $entityManager->getRepository('Lunches\Model\Product');
    }

    public function createFromArray($array)
    {
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
        return array_map(function (LineItem $lineItem) use ($price) {
            return new PriceItem($price, $lineItem->getProduct(), $lineItem->getSize());
        }, $order->getLineItems());
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

        return $priceItems;
    }

    private function createItem(Price $price, array $item)
    {
        if (!array_key_exists('size', $item)) {
            throw ValidationException::invalidPrice('Price item must have "size" of product specified');
        }

        if (!array_key_exists('productId', $item) || !$product = $this->productRepo->find($item['productId'])) {
            throw ValidationException::invalidPrice('Price item must contain valid product');
        }

        return new PriceItem($price, $product, $item['size']);
    }
}