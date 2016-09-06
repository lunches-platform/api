<?php


namespace Lunches\Tests\Model;


use Doctrine\Common\Collections\ArrayCollection;
use Lunches\Exception\ValidationException;
use Lunches\Model\LineItem;
use Lunches\Model\Order;
use Lunches\Model\Price;
use Lunches\Model\PriceFactory;
use Lunches\Model\Product;
use Lunches\Model\ProductRepository;

class PriceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  PriceFactory */
    protected $factory;
    public function setUp()
    {
        $this->factory = new PriceFactory($this->getProductRepo());
    }
    public function testCreateFromArray()
    {
        $price = $this->factory->createFromArray($this->getPriceData());

        self::assertInstanceOf(Price::class, $price);
        self::assertNotEmpty($price->getValue());
        self::assertNotEmpty($price->getDate());
        self::assertGreaterThan(0, $price->getItems()->count());
    }
    public function testDateEmpty()
    {
        $this->setExpectedException(ValidationException::class);
        $data = $this->getPriceData();
        unset($data['date']);
        $this->factory->createFromArray($data);
    }
    public function testItemsEmpty()
    {
        $this->setExpectedException(ValidationException::class);
        $data = $this->getPriceData();
        unset($data['items']);
        $this->factory->createFromArray($data);
    }
    public function testPriceEmpty()
    {
        $this->setExpectedException(ValidationException::class);
        $data = $this->getPriceData();
        unset($data['price']);
        $this->factory->createFromArray($data);
    }
    public function testInvalidDate()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid date provided. ');
        $this->factory->createFromArray($this->getPriceData(['date' => 'invalid date']));
    }
    public function testCreateEmptyItems()
    {
        $this->setExpectedException(ValidationException::class, 'Price must have at least one price item');
        $this->factory->createFromArray($this->getPriceData(['items' => []]));
    }
    public function testCreateItemNoSize()
    {
        $this->setExpectedException(ValidationException::class, 'Price item must have "size" of product specified');
        $this->factory->createFromArray($this->getPriceData(['items' => [
            [
                'productId' => $this->existProductId(),
            ],
        ]]));
    }
    public function testCreateItemNoProductId()
    {
        $this->setExpectedException(ValidationException::class, 'Price item must contain valid product');
        $this->factory->createFromArray($this->getPriceData(['items' => [
            [
                'size' => 'big',
            ],
        ]]));
    }
    public function testCreateItemProductNotFound()
    {
        $this->setExpectedException(ValidationException::class, 'Price item must contain valid product');
        $this->factory->createFromArray($this->getPriceData(['items' => [
            [
                'size' => 'big',
                'productId' => $this->notExistProductId(),
            ],
        ]]));
    }
    public function testCreatePriceItemsFromOrder()
    {
        $request = $this->getPriceData();
        $price = $this->factory->createFromArray($request);

        $lineItem = new LineItem();
        $lineItem->setSize($request['items'][0]['size']);
        $lineItem->setProduct($price->getItems()->first()->getProduct());

        $order = new Order();
        $order->setShipmentDate(new \DateTime($request['date']));
        $order->setPrice($request['price']);
        $order->setLineItems([ $lineItem ]);

        $priceItems = PriceFactory::createPriceItemsFromOrder($order, $price);
        self::assertInstanceOf(ArrayCollection::class, $priceItems);
        self::assertEquals($order->getLineItems()->count(), $priceItems->count());
    }

    private function existProductId()
    {
        return 'exist';
    }
    private function notExistProductId()
    {
        return 'notExist';
    }

    /**
     * @return ProductRepository
     */
    private function getProductRepo()
    {
        $productRepo = $this->getMockBuilder(ProductRepository::class)->disableOriginalConstructor()->getMock();
        $productRepo->method('find')->will(self::returnValueMap([
            [ $this->existProductId(),    null, null, new Product(1) ],
            [ $this->notExistProductId(), null, null, null ],
        ]));

        return $productRepo;
    }

    private function getPriceData(array $override = [])
    {
        return array_replace([
            'date' => (new \DateTime('tomorrow'))->format('Y-m-d'),
            'price' => 45,
            'items' => [
                [
                    'productId' => $this->existProductId(),
                    'size' => 'big',
                ]
            ],
        ], $override);
    }
}
