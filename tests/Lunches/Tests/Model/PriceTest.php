<?php


namespace Lunches\Tests\Model;


use Doctrine\Common\Collections\ArrayCollection;
use Lunches\Exception\ValidationException;
use Lunches\Model\Price;
use Lunches\Model\PriceItem;
use Lunches\Model\Product;

class PriceTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $price = new Price($value = 100, $date = new \DateTime('tomorrow'));
        $price->setItems($items = $this->createItems($price));

        self::assertEquals($value, $price->getValue());
        self::assertEquals($date, $price->getDate());
        /** @noinspection PhpUnitTestsInspection */
        self::assertEquals(count($items), $price->getItems()->count());
    }
    public function testToArray()
    {
        $price = new Price($value = 100, $date = new \DateTime('tomorrow'));
        $array = $price->toArray();
        
        self::assertTrue(is_array($array));
        self::assertArrayHasKey('date', $array);
        self::assertArrayHasKey('price', $array);
        self::assertArrayHasKey('items', $array);
        self::assertTrue(is_array($array['items']));
    }
    public function testGetValueWhenNoItemsAssigned()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid price provided. Price must be assigned to one or more items but zero given. Probably some of items are invalid');
        $price = new Price($value = 100, $date = new \DateTime());
        $price->getValue();
    }
    public function testSetItems()
    {
        $price = new Price(100, new \DateTime());
        $price->setItems($this->createItems($price));

        self::assertCount(2, $price->getItems());
    }
    public function testValueShouldBeFloat()
    {
        $price = new Price(100, new \DateTime());
        $price->setItems($this->createItems($price));

        self::assertTrue(is_float($price->getValue()));
    }
    public function testHasPriceItem()
    {
        $price = new Price(100, new \DateTime());

        list($priceItem1, $priceItem2) = $this->createItems($price);

        $price->setItems([$priceItem1]);

        self::assertTrue($price->hasPriceItem($priceItem1));
        self::assertFalse($price->hasPriceItem($priceItem2));
    }
    public function testHasPriceItemEmptyItems()
    {
        $price = new Price(100, new \DateTime());
        $priceItem = new PriceItem($price, new Product(1), 'big');

        self::assertFalse($price->hasPriceItem($priceItem));
    }
    public function tesPriceDateTodayOrInThePast()
    {
        $errMsg = 'Invalid date provided. Price date can not be today or in the past';
        try {
            new Price(100, new \DateTime('today'));
        } catch (ValidationException $e) {
            self::assertEquals($errMsg, $e->getMessage());
        }
        try {
            new Price(100, new \DateTime('yesterday'));
        } catch (ValidationException $e) {
            self::assertEquals($errMsg, $e->getMessage());
        }
    }
    public function testEqualsTo()
    {
        $price1 = new Price(100, new \DateTime('tomorrow'));
        $price2 = new Price(100, new \DateTime('tomorrow'));

        $price1->setItems($this->createItems($price1));
        $price2->setItems($this->createItems($price2));

        self::assertTrue($price1->equalsTo($price2));
    }
    public function testValuesAreNotEqual()
    {
        $price1 = new Price(100, new \DateTime('tomorrow'));
        $price2 = new Price(200, new \DateTime('tomorrow'));

        $price1->setItems($this->createItems($price1));
        $price2->setItems($this->createItems($price2));

        self::assertFalse($price1->equalsTo($price2));
    }
    public function testDatesAreNotEqual()
    {
        $price1 = new Price(100, new \DateTime('tomorrow'));
        $price2 = new Price(100, new \DateTime('next week'));

        $price1->setItems($this->createItems($price1));
        $price2->setItems($this->createItems($price2));

        self::assertFalse($price1->equalsTo($price2));
    }
    public function testItemsCountIsNotEqual()
    {
        $price1 = new Price(100, new \DateTime('tomorrow'));
        $price2 = new Price(100, new \DateTime('tomorrow'));

        $priceItem1 = new PriceItem($price1, new Product(1), 'big');
        $priceItem2 = new PriceItem($price2, new Product(2), 'small');
        $priceItem3 = new PriceItem($price2, new Product(3), 'medium');

        $price1->setItems($items1 = [$priceItem1]);
        $price2->setItems($items2 = [$priceItem2, $priceItem3]);

        self::assertFalse($price1->areItemsEquals(new ArrayCollection($items2)));
        self::assertNotEquals($price1->getItems()->count(), $price2->getItems()->count());
    }
    public function testItemsAreNotEqual()
    {
        $price1 = new Price(100, new \DateTime('tomorrow'));
        $price2 = new Price(100, new \DateTime('tomorrow'));

        $priceItem1 = new PriceItem($price1, new Product(1), 'big');
        $priceItem2 = new PriceItem($price1, new Product(2), 'small');
        $priceItem3 = new PriceItem($price1, new Product(3), 'medium');
        $priceItem4 = new PriceItem($price1, new Product(4), 'medium');

        $items1 = [$priceItem1, $priceItem2, $priceItem3];
        $items2 = [$priceItem1, $priceItem2, $priceItem4];

        $price1->setItems($items1);
        $price2->setItems($items2);

        self::assertFalse($price1->areItemsEquals(new ArrayCollection($items2)));
    }

    private function createItems($price)
    {
        $priceItem1 = new PriceItem($price, new Product(1), 'big');
        $priceItem2 = new PriceItem($price, new Product(2), 'small');
        
        return [$priceItem1, $priceItem2];
    }
}
