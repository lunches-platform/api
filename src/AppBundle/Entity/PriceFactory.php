<?php

namespace AppBundle\Entity;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use AppBundle\Exception\ValidationException;

class PriceFactory
{
    /** @var DishRepository  */
    protected $dishRepository;

    public function __construct(Registry $doctrine)
    {
        $this->dishRepository = $doctrine->getRepository('AppBundle:Dish');
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
        /** @var ArrayCollection $lineItems */
        $lineItems =$order->getLineItems();
        $values = $lineItems->getValues();

        return new ArrayCollection(array_map(function (LineItem $lineItem) use ($price) {
            return new PriceItem($price, $lineItem->getDish(), $lineItem->getSize());
        }, $values));
    }

    private function createDate($date)
    {
        if (!$date instanceof \DateTimeImmutable) {
            throw ValidationException::invalidDate();
        }
        return $date;
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
            throw ValidationException::invalidPrice('Price item must have "size" of dish specified');
        }

        // TODO use DishRepository::get() instead of find() and remove check for dish existence as it is not responsibility of this method
        if (!array_key_exists('dishId', $item) || !$dish = $this->dishRepository->find($item['dishId'])) {
            throw ValidationException::invalidPrice('Price item must contain valid dish');
        }

        return new PriceItem($price, $dish, $item['size']);
    }
}