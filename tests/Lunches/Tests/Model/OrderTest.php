<?php

namespace Lunches\Tests\Model;

use Lunches\Exception\OrderException;
use Lunches\Exception\ValidationException;
use Lunches\Model\LineItem;
use Lunches\Model\Order;
use Lunches\Model\Transaction;

class OrderTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');
    }
    public function testOrderToArray()
    {
        self::markTestSkipped('Failed due to Fatal error');
        $order = new Order();

        self::assertTrue(is_array($order->toArray()));
    }

    public function testAddLineItem()
    {
        $order = new Order();
        $lineItem = new LineItem();

        $order->addLineItem($lineItem);

        self::assertSame($lineItem, $order->getLineItems()[0]);
    }

    public function testAddLineItems()
    {
        $order = new Order();
        $lineItem1 = new LineItem();
        $lineItem2 = new LineItem();

        $order->addLineItem($lineItem1);
        $order->addLineItem($lineItem2);

        self::assertCount(2, $order->getLineItems());
    }

    public function testOrderCreatedStatus()
    {
        $order = new Order();
        self::assertEquals(Order::STATUS_CREATED, $order->currentStatus());
    }

    public function testPay()
    {
        $order = new Order();
        $transaction = $order->pay();

        self::assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @group integration
     */
    public function testPayWithoutPrice()
    {
        $order = new Order();
        $result = $order->pay();

        self::assertFalse($result);
    }

    public function testPayClosedOrder()
    {
        $order = new Order();
        $order->close();
        $result = $order->pay();

        self::assertTrue($result);
    }

    public function testChangeAddress()
    {
        $order = new Order();
        $order->setAddress($address = 'New York, US');
        self::assertEquals($address, $order->getAddress());

        $order->changeAddress($address = 'San Francisco, US');
        self::assertEquals($address, $order->getAddress());
    }

    public function testChangeAddressOfFulfilledOrder()
    {
        $this->setExpectedException(OrderException::class);

        $order = new Order();
        $order->startProgress();
        $order->changeAddress('some address');
    }

    public function testSetEmptyAddress()
    {
        $this->setExpectedException(ValidationException::class);

        $order = new Order();
        $order->setAddress('');
    }
    public function testSetTooLongAddress()
    {
        $this->setExpectedException(ValidationException::class);

        $order = new Order();
        $order->setAddress(str_repeat('a', 151));
    }
}
