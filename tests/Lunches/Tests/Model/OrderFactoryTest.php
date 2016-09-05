<?php


namespace Lunches\Tests\Model;

use Lunches\Exception\RuntimeException;
use Lunches\Exception\ValidationException;
use Lunches\Model\Menu;
use Lunches\Model\MenuProduct;
use Lunches\Model\MenuRepository;
use Lunches\Model\Order;
use Lunches\Model\OrderFactory;
use Lunches\Model\OrderRepository;
use Lunches\Model\Price;
use Lunches\Model\PriceRepository;
use Lunches\Model\Prices;
use Lunches\Model\Product;
use Lunches\Model\ProductRepository;
use Lunches\Model\User;
use Lunches\Model\UserRepository;

class OrderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  OrderFactory */
    protected $factory;
    public function setUp()
    {
        $this->factory = new OrderFactory(
            $this->getOrderRepo(),
            $this->getProductRepo(),
            $this->getMenuRepo(),
            $this->getPriceRepo(),
            $this->getUserRepo()
        );
    }
    public function testCreateWithNoData()
    {
        $orderClass = Order::class;
        self::assertInstanceOf($orderClass, $this->factory->createNewFromArray([]));
    }

    public function testOrderNumber()
    {
        $order = $this->factory->createNewFromArray($this->getOrderData());

        self::assertNotEmpty($order->getOrderNumber());
    }

    public function testOrderUser()
    {
        $order = $this->factory->createNewFromArray($this->getOrderData(['userId' => $this->existId()]));

        self::assertInstanceOf(User::class, $order->getUser());
    }

    public function testUserIdNotProvided()
    {
        $this->setExpectedException(ValidationException::class, 'Each order must have userId');
        $this->factory->createNewFromArray(['someKey' => 'someValue']);
    }

    public function testUserNotFound()
    {
        $this->setExpectedException(RuntimeException::class, 'User not found');

        $this->factory->createNewFromArray(['userId' => $this->notExistId()]);
    }

    public function testOrderAddress()
    {
        $data = $this->getOrderData(['address' => $address = 'Some address']);

        $order = $this->factory->createNewFromArray($data);

        self::assertEquals($address, $order->getAddress());
    }


    public function testOrderAddressFromUser()
    {
        $data = $this->getOrderData(['userId' => $userId = $this->existId()]);

        $order = $this->factory->createNewFromArray($data);

        self::assertEquals($this->getUser($userId)->getAddress(), $order->getAddress());
    }
    public function testOrderDate()
    {
        $data = $this->getOrderData(['shipmentDate' => $this->tomorrowDate()]);
        $order = $this->factory->createNewFromArray($data);

        self::assertEquals($this->tomorrowDate(true), $order->getShipmentDate());
    }
    public function testDateIsNotProvided()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid order. Date field is not provided');
        
        $data = $this->getOrderData();
        unset($data['shipmentDate']);
        $this->factory->createNewFromArray($data);
    }
    public function testDateIsEmpty()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid date provided. Date must be specified');

        $this->factory->createNewFromArray($this->getOrderData(['shipmentDate' => '']));
    }
    public function testInvalidDate()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid date provided. ');

        $this->factory->createNewFromArray($this->getOrderData(['shipmentDate' => '$&#(@']));
    }
    public function testOrderForToday()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid date provided. Can not order product for today or in the past');
        $order = $this->factory->createNewFromArray($this->getOrderData(['shipmentDate' => $date = (new \DateTime())->format('Y-m-d')]));

        self::assertEquals($date, $order->getShipmentDate());
    }
    public function testOrderInThePast()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid date provided. Can not order product for today or in the past');
        $order = $this->factory->createNewFromArray($this->getOrderData(['shipmentDate' => $date = (new \DateTime('yesterday'))->format('Y-m-d')]));

        self::assertEquals($date, $order->getShipmentDate());
    }

    public function testNoLineItems()
    {
        self::markTestIncomplete();
    }
    public function testLineItemsAreNotArray()
    {
        self::markTestIncomplete();
    }
    public function testLeaveOnlyUniqueLineItems()
    {
        self::markTestIncomplete();
    }
    public function testNoValidLineItems()
    {
        self::markTestIncomplete();
    }
    public function testLineItemRequiredFields()
    {
        // TODO make two assertions: for productId and for size
        self::markTestIncomplete();
    }
    public function testLineItemHasInvalidSize()
    {
        self::markTestIncomplete();
    }
    public function testLineItemWithNotFoundProduct()
    {
        self::markTestIncomplete();
    }
    public function testLineItemProductNotCookingToday()
    {
        self::markTestIncomplete();
    }
    public function testMenuNotFound()
    {
        self::markTestIncomplete();
    }



    private function getOrderRepo()
    {
        $orderRepo = $this->getMockBuilder(OrderRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['generateOrderNumber'])
            ->getMock();

        $orderRepo->method('generateOrderNumber')->willReturn('1');
        
        return $orderRepo;
    }

    private function getProductRepo()
    {
        $productRepo = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $products = [
            $this->existId() => new Product(),
        ];

        $map = [
            [$this->existId(), $products[$this->existId()]],
            [$this->notExistId(), null],
        ];
        $productRepo->method('get')->will(self::returnValueMap($map));

        return $productRepo;
    }

    private function getMenuRepo()
    {
        $menuRepo = $this->getMockBuilder(MenuRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $menu = new Menu();
        $menu->addProduct(new MenuProduct($menu, $this->getProductRepo()->get($this->existId())));

        $menuRepo->method('findByDate')->willReturn([$menu]);

//        $menuRepo->method('findByDate')->will(self::returnCallback(function (\DateTime $date) {
////            if ($date === ...) {
////
////            }
//            return new Menu();
//        }));
        
        return $menuRepo;
    }

    private function getPriceRepo()
    {
        $priceRepo = $this->getMockBuilder(PriceRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $priceRepo->method('findByDate')->willReturn(new Prices([new Price(100, $this->tomorrowDate(true))]));
        
        return $priceRepo;
    }

    private function getUserRepo()
    {
        $userRepo = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $map = [
            [$this->existId(), null, null, $this->getUser($this->existId())],
            [$this->notExistId(), null, null, null],
        ];
        $userRepo->method('find')->will(self::returnValueMap($map));

        return $userRepo;
    }

    private function getUser($userId)
    {
        if ($userId === $this->existId()) {
            return new User($userId, 'username', 'Some address');
        }

        return null;
    }
    private function existId()
    {
        return 'exist';
    }

    private function notExistId()
    {
        return 'notExist';
    }
    private function tomorrowDate($asDateTime = false)
    {
        $tomorrow = new \DateTime('tomorrow');
        if ($asDateTime ===  true) {
            return $tomorrow;
        }
        return $tomorrow->format('Y-m-d');
    }
    private function getOrderData(array $override = [])
    {
        $data = [
            'address' => $address = 'Some address',
            'userId' => $this->existId(),
            'shipmentDate' => $this->tomorrowDate(),
            'items' => [
                [
                    'productId' => $this->existId(),
                    'size' => 'big'
                ]
            ],
        ];

        return array_replace($data, $override);
    }
}
