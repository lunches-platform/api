<?php


namespace Lunches\Tests\Model;

use Lunches\Exception\LineItemException;
use Lunches\Exception\RuntimeException;
use Lunches\Exception\ValidationException;
use Lunches\Model\Menu;
use Lunches\Model\MenuProduct;
use Lunches\Model\MenuRepository;
use Lunches\Model\Order;
use Lunches\Model\OrderFactory;
use Lunches\Model\OrderRepository;
use Lunches\Model\Price;
use Lunches\Model\PriceItem;
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
        $data = $this->getOrderData(['shipmentDate' => $this->getDate('tomorrow')]);
        $order = $this->factory->createNewFromArray($data);

        self::assertEquals($this->getDate('tomorrow', true), $order->getShipmentDate());
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
        $this->setExpectedException(ValidationException::class, 'Invalid order. There are no valid LineItems provided');

        $data = $this->getOrderData();
        unset($data['items']);
        $this->factory->createNewFromArray($data);
    }
    public function testLineItemsAreNotArray()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid order. There are no valid LineItems provided');

        $this->factory->createNewFromArray($this->getOrderData(['items' => 'string instead of array']));
    }
    public function testLeaveOnlyUniqueLineItems()
    {
        $order = $this->factory->createNewFromArray($this->getOrderData([
            'items' => [
                [
                    'size' => 'big',
                    'productId' => $this->existId(),
                ],
                [
                    'size' => 'big',
                    'productId' => $this->existId(),
                ]
            ]
        ]));
        
        self::assertCount(1, $order->getLineItems());
    }
    public function testLineItemRequiredFields()
    {
        $errMsg = 'Invalid LineItem. There are no required fields. Required are productId, size';

        $emptySizeLineItem = [
            'productId' => 'some'
        ];
        $emptyProductIdLineItem = [
            'size' => 'big'
        ];
        try {
            $this->factory->createNewFromArray($this->getOrderData(['items' => [$emptySizeLineItem]]));
        } catch (ValidationException $e) {
            self::assertEquals($errMsg, $e->getMessage());
        }
        try {
            $this->factory->createNewFromArray($this->getOrderData(['items' => [$emptyProductIdLineItem]]));
        } catch (ValidationException $e) {
            self::assertEquals($errMsg, $e->getMessage());
        }
    }
    public function testLineItemHasInvalidSize()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid size provided.');

        $this->factory->createNewFromArray($this->getOrderData(['items' => [
            [
                'size' => 'not allowed size name',
                'productId' => $this->existId(),
            ]
        ]]));
    }
    public function testLineItemWithNotFoundProduct()
    {
        $this->setExpectedException(RuntimeException::class, 'Product not found');

        $this->factory->createNewFromArray($this->getOrderData(['items' => [
            [
                'size' => 'big',
                'productId' => $this->notExistId(),
            ]
        ]]));
    }
    public function testLineItemProductNotCookingToday()
    {
        $this->setExpectedException(LineItemException::class);

        $this->factory->createNewFromArray($this->getOrderData(['items' => [
            [
                'size' => 'big',
                'productId' => $this->notCookingProduct(),
            ]
        ]]));
    }
    public function testMenuNotFound()
    {
        $this->setExpectedException(RuntimeException::class);

        $this->factory->createNewFromArray($this->getOrderData([
            'shipmentDate' => (new \DateTime('+2 day'))->format('Y-m-d'),
            'items' => [
                [
                    'size' => 'big',
                    'productId' => $this->existId(),
                ]
            ]
        ]));
    }

    public function testPriceShouldBeFloat()
    {
        $order = $this->factory->createNewFromArray($this->getOrderData(['shipmentDate' => $this->getDate('tomorrow')]));
        
        self::assertTrue(is_float($order->getPrice()));
    }
    public function testPricesNotFound()
    {
        $this->setExpectedException(RuntimeException::class);
        $this->factory->createNewFromArray($this->getOrderData(['shipmentDate' => $this->getDate('next week')]));
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
            ->setMethods(['find'])
            ->getMock();

        $products = [
            $this->existId() => new Product($this->existId()),
            $this->notCookingProduct() => new Product($this->notCookingProduct()),
        ];

        $map = [
            [$this->existId(), null, null, $products[$this->existId()]],
            [$this->notCookingProduct(), null, null, $products[$this->notCookingProduct()]],
            [$this->notExistId(), null, null, null],
        ];
        $productRepo->method('find')->will(self::returnValueMap($map));

        return $productRepo;
    }

    private function getMenuRepo()
    {
        $menuRepo = $this->getMockBuilder(MenuRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $menuRepo->method('findByDate')->will(self::returnCallback(function (\DateTime $date) {
            /** @noinspection TypeUnsafeComparisonInspection */
            if ($date == $this->getDate('tomorrow', true)) {
                /** @var ProductRepository $productRepo */
                $productRepo = $this->getProductRepo();
                $product = $productRepo->get($this->existId());
                $menu = new Menu();
                $menu->addProduct(new MenuProduct($menu, $product));

                return [$menu];
            }

            return [];
        }));
        
        return $menuRepo;
    }

    private function getPriceRepo()
    {
        $priceRepo = $this->getMockBuilder(PriceRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $priceRepo->method('findByDate')->will(self::returnCallback(function (\DateTime $dateTime) {

            $tomorrow = $this->getDate('tomorrow', true);
            
            if ($dateTime == $tomorrow) {
                /** @var ProductRepository $productRepo */
                $productRepo = $this->getProductRepo();

                $price = new Price(100, $tomorrow);
                $price->setItems([
                    new PriceItem($price, $productRepo->get($this->existId()), 'big'),
                ]);
                    
                return new Prices([$price]);
            }
            return new Prices();
        }));

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
    private function notCookingProduct()
    {
        return 'notCooking';
    }
    private function getDate($time, $asDateTime = false)
    {
        $tomorrow = new \DateTime($time);
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
            'shipmentDate' => $this->getDate('tomorrow'),
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
