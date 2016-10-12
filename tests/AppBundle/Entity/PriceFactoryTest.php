<?php


namespace Tests\AppBundle\Entity;


use AppBundle\Entity\Dish;
use AppBundle\Entity\DishRepository;
use AppBundle\Entity\LineItem;
use AppBundle\Entity\Order;
use AppBundle\Entity\Price;
use AppBundle\Entity\PriceFactory;
use AppBundle\Exception\ValidationException;
use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;

class PriceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  PriceFactory */
    protected $factory;
    public function setUp()
    {
        $doctrine = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine->method('getRepository')->willReturn($this->getDishRepo());
        $this->factory = new PriceFactory($doctrine);
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
        $this->setExpectedException(ValidationException::class, 'Price item must have "size" of dish specified');
        $this->factory->createFromArray($this->getPriceData(['items' => [
            [
                'dishId' => $this->existDishId(),
            ],
        ]]));
    }
    public function testCreateItemNoDishId()
    {
        $this->setExpectedException(ValidationException::class, 'Price item must contain valid dish');
        $this->factory->createFromArray($this->getPriceData(['items' => [
            [
                'size' => 'big',
            ],
        ]]));
    }
    public function testCreateItemDishNotFound()
    {
        $this->setExpectedException(ValidationException::class, 'Price item must contain valid dish');
        $this->factory->createFromArray($this->getPriceData(['items' => [
            [
                'size' => 'big',
                'dishId' => $this->notExistDishId(),
            ],
        ]]));
    }
    public function testCreatePriceItemsFromOrder()
    {
        $request = $this->getPriceData();
        $price = $this->factory->createFromArray($request);

        $lineItem = new LineItem();
        $lineItem->setSize($request['items'][0]['size']);
        $lineItem->setDish($price->getItems()->first()->getDish());

        $order = new Order();
        $order->setShipmentDate($request['date']);
        $order->setPrice($request['price']);
        $order->setLineItems([ $lineItem ]);

        $priceItems = PriceFactory::createPriceItemsFromOrder($order, $price);
        self::assertInstanceOf(ArrayCollection::class, $priceItems);
        self::assertEquals($order->getLineItems()->count(), $priceItems->count());
    }

    private function existDishId()
    {
        return 'exist';
    }
    private function notExistDishId()
    {
        return 'notExist';
    }

    /**
     * @return DishRepository
     */
    private function getDishRepo()
    {
        $dishRepo = $this->getMockBuilder(DishRepository::class)->disableOriginalConstructor()->getMock();
        $dishRepo->method('find')->will(self::returnValueMap([
            [ $this->existDishId(),    null, null, new Dish(1) ],
            [ $this->notExistDishId(), null, null, null ],
        ]));

        return $dishRepo;
    }

    private function getPriceData(array $override = [])
    {
        return array_replace([
            'date' => new \DateTime('tomorrow'),
            'price' => 45,
            'items' => [
                [
                    'dishId' => $this->existDishId(),
                    'size' => 'big',
                ]
            ],
        ], $override);
    }
}
