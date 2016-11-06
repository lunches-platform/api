<?php


namespace Tests\AppBundle;


use AppBundle\Entity\Dish;
use AppBundle\Entity\DishRepository;
use AppBundle\Entity\Menu;
use AppBundle\Entity\MenuDish;
use AppBundle\Entity\MenuRepository;
use AppBundle\Entity\Order;
use AppBundle\Entity\OrderRepository;
use AppBundle\Entity\Price;
use AppBundle\Entity\PriceItem;
use AppBundle\Entity\PriceRepository;
use AppBundle\Entity\Prices;
use AppBundle\Entity\User;
use AppBundle\Entity\UserRepository;
use AppBundle\Exception\LineItemException;
use AppBundle\Exception\RuntimeException;
use AppBundle\Exception\ValidationException;
use AppBundle\OrderFactory;
use Doctrine\Bundle\DoctrineBundle\Registry;

class OrderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var  OrderFactory */
    protected $factory;
    public function setUp()
    {
        $doctrine = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();

        $doctrine->method('getRepository')->will(self::onConsecutiveCalls(
            $this->getOrderRepo(),
            $this->getDishRepo(),
            $this->getMenuRepo(),
            $this->getPriceRepo(),
            $this->getUserRepo()
        ));

        $this->factory = new OrderFactory($doctrine);
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
        $data = $this->getOrderData(['shipmentDate' => $this->getDate('tomorrow + 1day')]);
        $order = $this->factory->createNewFromArray($data);

        self::assertEquals($this->getDate('tomorrow + 1day', true), $order->getShipmentDate());
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
        $this->setExpectedException(ValidationException::class, 'Invalid date provided. Can not order dish for today or in the past');
        $order = $this->factory->createNewFromArray($this->getOrderData(['shipmentDate' => $date = (new \DateTimeImmutable())->format('Y-m-d')]));

        self::assertEquals($date, $order->getShipmentDate());
    }
    public function testOrderInThePast()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid date provided. Can not order dish for today or in the past');
        $order = $this->factory->createNewFromArray($this->getOrderData(['shipmentDate' => $date = (new \DateTimeImmutable('yesterday'))->format('Y-m-d')]));

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
                    'dishId' => $this->existId(),
                ],
                [
                    'size' => 'big',
                    'dishId' => $this->existId(),
                ]
            ]
        ]));
        
        self::assertCount(1, $order->getLineItems());
    }
    public function testLineItemRequiredFields()
    {
        $errMsg = 'Invalid LineItem. There are no required fields. Required are dishId, size';

        $emptySizeLineItem = [
            'dishId' => 'some'
        ];
        $emptyDishIdLineItem = [
            'size' => 'big'
        ];
        try {
            $this->factory->createNewFromArray($this->getOrderData(['items' => [$emptySizeLineItem]]));
        } catch (ValidationException $e) {
            self::assertEquals($errMsg, $e->getMessage());
        }
        try {
            $this->factory->createNewFromArray($this->getOrderData(['items' => [$emptyDishIdLineItem]]));
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
                'dishId' => $this->existId(),
            ]
        ]]));
    }
    public function testLineItemWithNotFoundDish()
    {
        $this->setExpectedException(RuntimeException::class, 'Dish not found');

        $this->factory->createNewFromArray($this->getOrderData(['items' => [
            [
                'size' => 'big',
                'dishId' => $this->notExistId(),
            ]
        ]]));
    }
    public function testLineItemDishNotCookingToday()
    {
        $this->setExpectedException(LineItemException::class);

        $this->factory->createNewFromArray($this->getOrderData(['items' => [
            [
                'size' => 'big',
                'dishId' => $this->notCookingDish(),
            ]
        ]]));
    }
    public function testMenuNotFound()
    {
        $this->setExpectedException(RuntimeException::class);

        $this->factory->createNewFromArray($this->getOrderData([
            'shipmentDate' => (new \DateTimeImmutable('tomorrow +2day'))->format('Y-m-d'),
            'items' => [
                [
                    'size' => 'big',
                    'dishId' => $this->existId(),
                ]
            ]
        ]));
    }

    public function testPriceShouldBeFloat()
    {
        $order = $this->factory->createNewFromArray($this->getOrderData(['shipmentDate' => $this->getDate('tomorrow + 1day')]));
        
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

    private function getDishRepo()
    {
        $dishRepo = $this->getMockBuilder(DishRepository::class)
            ->disableOriginalConstructor()
            ->setMethods(['find'])
            ->getMock();

        $dishes = [
            $this->existId() => new Dish($this->existId()),
            $this->notCookingDish() => new Dish($this->notCookingDish()),
        ];

        $map = [
            [$this->existId(), null, null, $dishes[$this->existId()]],
            [$this->notCookingDish(), null, null, $dishes[$this->notCookingDish()]],
            [$this->notExistId(), null, null, null],
        ];
        $dishRepo->method('find')->will(self::returnValueMap($map));

        return $dishRepo;
    }

    private function getMenuRepo()
    {
        $menuRepo = $this->getMockBuilder(MenuRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $menuRepo->method('findByDate')->will(self::returnCallback(function (\DateTimeImmutable $date) {
            /** @noinspection TypeUnsafeComparisonInspection */
            if ($date == $this->getDate('tomorrow + 1day', true)) {
                /** @var DishRepository $dishRepo */
                $dishRepo = $this->getDishRepo();
                $dish = $dishRepo->get($this->existId());
                $menu = new Menu($date, 'regular');
                $menu->addDish(new MenuDish($menu, $dish, 0));

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

        $priceRepo->method('findByDate')->will(self::returnCallback(function (\DateTimeImmutable $dateTime) {

            $tomorrow = $this->getDate('tomorrow + 1day', true);
            
            if ($dateTime == $tomorrow) {
                /** @var DishRepository $dishRepo */
                $dishRepo = $this->getDishRepo();

                $price = new Price(100, $tomorrow);
                $price->setItems([
                    new PriceItem($price, $dishRepo->get($this->existId()), 'big'),
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
    private function notCookingDish()
    {
        return 'notCooking';
    }
    private function getDate($time, $asDateTime = false)
    {
        $dateTime = new \DateTimeImmutable($time);
        if ($asDateTime ===  true) {
            return $dateTime;
        }
        return $dateTime->format('Y-m-d');
    }
    private function getOrderData(array $override = [])
    {
        $data = [
            'address' => $address = 'Some address',
            'userId' => $this->existId(),
            'shipmentDate' => $this->getDate('tomorrow + 1day'),
            'items' => [
                [
                    'dishId' => $this->existId(),
                    'size' => 'big'
                ]
            ],
        ];

        return array_replace($data, $override);
    }
}
