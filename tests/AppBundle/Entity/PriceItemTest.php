<?php


namespace Tests\AppBundle\Entity;

use AppBundle\Entity\Dish;
use AppBundle\Entity\Price;
use AppBundle\Entity\PriceItem;
use AppBundle\Exception\ValidationException;

class PriceItemTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $price = new Price(100, new \DateTime());
        $product = new Dish(1);
        $size = 'big';

        $priceItem = new PriceItem($price, $product, $size);

        self::assertEquals($price, $priceItem->getPrice());
        self::assertEquals($product, $priceItem->getDish());
        self::assertEquals($size, $priceItem->getSize());
    }

    public function testInvalidSize()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid size provided.');

        $size = 'invalid size';

        new PriceItem(
            new Price(100, new \DateTime()),
            new Dish(1),
            $size
        );
    }

    public function testEquals()
    {
        $priceItem1 = $this->getPriceItem();
        $priceItem2 = $this->getPriceItem();

        self::assertTrue($priceItem1->equalsTo($priceItem2));
    }
    public function testSizeNotEqual()
    {
        $priceItem1 = $this->getPriceItem(null, null, $size = 'big');
        $priceItem2 = $this->getPriceItem(null, null, $size = 'medium');

        self::assertFalse($priceItem1->equalsTo($priceItem2));
    }
    public function testDishNotEqual()
    {
        $priceItem1 = $this->getPriceItem(null, new Dish(1));
        $priceItem2 = $this->getPriceItem(null, new Dish(2));

        self::assertFalse($priceItem1->equalsTo($priceItem2));
    }

    /**
     * @param Price|null $price
     * @param Dish|null $product
     * @param string $size
     * @return PriceItem
     */
    private function getPriceItem($price = null, $product = null, $size = 'big')
    {
        $price = $price ?: new Price(100, new \DateTime());
        $product = $product?: new Dish(1);
        $size = $size?:'big';

        return new PriceItem($price, $product, $size);
    }
}
