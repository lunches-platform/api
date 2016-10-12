<?php


namespace Tests\AppBundle\Entity;


use AppBundle\Entity\Dish;
use AppBundle\Entity\LineItem;
use AppBundle\Entity\Order;
use AppBundle\Entity\Price;
use AppBundle\Entity\PriceItem;
use AppBundle\Entity\Prices;
use AppBundle\Exception\RuntimeException;

class PricesTest extends \PHPUnit_Framework_TestCase
{
    public function testToArray()
    {
        $prices = $this->createValidPrices();
        $pricesArray = $prices->toArray();

        self::assertTrue(is_array($pricesArray));

        foreach ($pricesArray as $item) {
            self::assertTrue(is_array($item));
        }
    }

    public function testToArrayEmptyValues()
    {
        $prices = new Prices();
        $pricesArray = $prices->toArray();

        self::assertEmpty($pricesArray);
        self::assertTrue(is_array($pricesArray));
    }
    public function testToArrayGroupByDate()
    {
        $prices = $this->createValidPrices();
        $pricesArray = $prices->toArray(true);

        $format = 'Y-m-d';
        $today = (new \DateTime())->format($format);
        $tomorrow = (new \DateTime('tomorrow'))->format($format);
        
        self::assertTrue(is_array($pricesArray));
        self::assertEquals(5, $prices->count());
        self::assertCount(2, $pricesArray);
        self::assertCount(4, $pricesArray[$today]);
        self::assertCount(1, $pricesArray[$tomorrow]);
    }
    public function testFilterOnlySingleItemPrices()
    {
        $price1 = new Price(45, new \DateTime());
        $price1->setItems([
            new PriceItem($price1, new Dish(1), 'small'),
        ]);

        $price2 = new Price(70, new \DateTime());
        $price2->setItems([
            new PriceItem($price2, new Dish(1), 'big'),
            new PriceItem($price2, new Dish(2), 'big')
        ]);

        $prices = new Prices([$price1, $price2]);
        $prices = $prices->getSingleItemPrices();

        self::assertEquals($price1, $prices->first());
    }
    public function testFindLineItemPrice()
    {
        $lineItem = new LineItem();
        $lineItem->setDish(new Dish(1));
        $lineItem->setSize('big');

        $price1 = new Price(45, new \DateTime());
        $price1->setItems([
            new PriceItem($price1, new Dish(1), 'small'),
        ]);
        $price2 = new Price(55, new \DateTime());
        $price2->setItems([
            new PriceItem($price2, new Dish(1), 'big'),
        ]);

        $prices = new Prices([$price1, $price2]);
        $lineItemPrice = $prices->getLineItemPrice($lineItem);

        self::assertTrue($lineItemPrice->equalsTo($price2));
    }
    public function testLineItemPriceNotFound()
    {
        $this->setExpectedException(RuntimeException::class, 'Price not found for dish #3');

        $lineItem = new LineItem();
        $lineItem->setDish(new Dish(3));
        $lineItem->setSize('small');

        $prices = $this->createValidPrices();
        $prices->getLineItemPrice($lineItem);
    }
    public function testFindOrderPriceForSpecifiedDate()
    {
        $order = $this->getOrder();
        $order->setShipmentDate(new \DateTime('tomorrow'));

        $prices = $this->createValidPrices();
        $orderPrice = $prices->getOrderPrice($order);

        self::assertEquals(75, $orderPrice);
    }

    public function testFindOrderPriceBySumOfProductPrices()
    {
        $order = $this->getOrder('big', 3, 4);
        $prices = $this->createValidPrices();

        $orderPriceBySum = $prices->getOrderPrice($order);

        self::assertEquals(55, $orderPriceBySum);
    }

    private function createValidPrices()
    {
        $price1 = new Price(25, new \DateTime());
        $price1->setItems([
            new PriceItem($price1, new Dish(3), 'big'),
        ]);
        $price2 = new Price(30, new \DateTime());
        $price2->setItems([
            new PriceItem($price2, new Dish(4), 'big')
        ]);
        $price3 = new Price(45, new \DateTime());
        $price3->setItems([
            new PriceItem($price3, new Dish(1), 'small'),
            new PriceItem($price3, new Dish(2), 'small')
        ]);

        $price4 = new Price(70, new \DateTime());
        $price4->setItems([
            new PriceItem($price4, new Dish(1), 'big'),
            new PriceItem($price4, new Dish(2), 'big')
        ]);

        // tomorrow
        $price5 = new Price(75, new \DateTime('tomorrow'));
        $price5->setItems([
            new PriceItem($price5, new Dish(1), 'big'),
            new PriceItem($price5, new Dish(2), 'big')
        ]);

        return new Prices([ $price1, $price2, $price3, $price4, $price5 ]);
    }

    private function getOrder($size = 'big', $dish1Id = 1, $dish2Id = 2)
    {
        $size = $size ?: 'big';
        $dish1 = new Dish($dish1Id);
        $dish2 = new Dish($dish2Id);

        $lineItem1 = new LineItem();
        $lineItem1->setSize($size);
        $lineItem1->setDish($dish1);

        $lineItem2 = new LineItem();
        $lineItem2->setSize($size);
        $lineItem2->setDish($dish2);

        $order = new Order();
        $order->setLineItems([ $lineItem1, $lineItem2 ]);

        return $order;
    }
}
