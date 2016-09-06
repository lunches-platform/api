<?php

namespace Lunches\Tests\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Lunches\Exception\OrderException;
use Lunches\Exception\ValidationException;
use Lunches\Model\LineItem;
use Lunches\Model\Order;
use Lunches\Model\Transaction;
use Lunches\Model\User;

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

    public function testSetLineItems()
    {
        $order = new Order();
        $lineItem1 = new LineItem();
        $lineItem2 = new LineItem();

        $order->setLineItems([$lineItem1, $lineItem2]);

        self::assertInstanceOf(ArrayCollection::class, $order->getLineItems());
        self::assertSame($lineItem1, $order->getLineItems()[0]);
        self::assertSame($lineItem2, $order->getLineItems()[1]);
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
        $order->setPrice(100);
        $order->setUser($this->userWithPositiveBalance());
        $transaction = $order->pay();

        self::assertInstanceOf(Transaction::class, $transaction);
    }

    /**
     * @group integration
     * @throws \Lunches\Exception\OrderException
     */
    public function testPayWithoutPrice()
    {
        $order = new Order();
        $result = $order->pay();

        self::assertFalse($result);
    }
    public function testAlreadyPaidOrder()
    {
        $order = $this->getPaidOrder();
        $result = $order->pay();

        self::assertFalse($result);
    }

    public function testPayInCredit()
    {
        $order = new Order();
        $order->setPrice($price = 45);
        $order->setUser($user = new User('1', 'name', 'address'));

        $result = $order->pay();
        self::assertFalse($result);

        self::assertEquals($price, $user->currentCredit());

        return $order;
    }

    /**
     * @depends testPayInCredit
     * @param Order $order
     * @throws OrderException
     */
    public function testPayCredit(Order $order)
    {
        $user = $order->getUser();
        $user->rechargeBalance(100);

        $transaction = $order->pay();

        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertEquals(0, $user->currentCredit());
    }

    /**
     * @throws OrderException
     * @throws \Lunches\Exception\ValidationException
     */
    public function testTwoTimesFailedPay()
    {
        $order = new Order();
        $order->setPrice($price = 45);
        $order->setUser($user = new User('1', 'name', 'address'));

        $result = $order->pay();
        self::assertFalse($result);
        self::assertEquals($price, $user->currentCredit());

        $result = $order->pay();
        self::assertFalse($result);
        self::assertEquals($price, $user->currentCredit());
    }

    public function testPayClosedOrder()
    {
        $order = $this->getPaidAndDeliveredOrder();
        $order->close();
        $result = $order->pay();

        self::assertTrue($result);
    }

    public function testPayCanceled()
    {
        $this->setExpectedException(OrderException::class);
        $order = new Order();
        $order->cancel();
        $order->pay();
    }
    public function testPayRejected()
    {
        $this->setExpectedException(OrderException::class);
        $order = new Order();
        $order->reject('Bad product');
        $order->pay();
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

    public function testStartProgress()
    {
        $order = new Order();
        $order->startProgress();
        
        self::assertEquals(Order::STATUS_IN_PROGRESS, $order->currentStatus());
    }

    public function testStartProgressFulfilledOrder()
    {
        $this->setExpectedException(OrderException::class);

        $order = $this->getPaidAndDeliveredOrder();
        $order->startProgress();
    }

    public function testDeliverOrder()
    {
        $order = new Order();
        $order->startProgress();
        $order->deliver('Carrier');

        self::assertEquals(Order::STATUS_DELIVERED, $order->currentStatus());
    }

    public function testDeliverJustCreatedOrder()
    {
        $this->setExpectedException(OrderException::class);

        $order = new Order();
        $order->deliver('Carrier');
    }

    public function testCloseOrder()
    {
        $order = $this->getPaidAndDeliveredOrder();
        $order->close();

        self::assertEquals(Order::STATUS_CLOSED, $order->currentStatus());
    }

    public function testCloseNonPaidAndNonDeliveredOrder()
    {
        $this->setExpectedException(OrderException::class);

        $order = new Order();
        $order->close();
    }

    public function testCancelOrder()
    {
        $order = new Order();
        $result = $order->cancel();

        self::assertEquals(Order::STATUS_CANCELED, $order->currentStatus());
        self::assertTrue($result);
    }

    public function testCancelPaidOrder()
    {
        $order = $this->getPaidOrder();
        $transaction = $order->cancel();

        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertEquals(Transaction::TYPE_REFUND, $transaction->type());
        self::assertEquals($order->getPrice(), $transaction->getAmount());
        self::assertSame($order->getUser(), $transaction->getUser());
    }

    public function testCancelOrderWithReason()
    {
        self::markTestIncomplete('Can not access reason now');

        $order = new Order();
        $order->cancel($reason = 'Bad product');
    }

    public function testCancelStarted()
    {
        $this->setExpectedException(OrderException::class);

        $order = new Order();
        $order->startProgress();
        $order->cancel();
    }

    public function testReject()
    {
        self::markTestIncomplete('Assert for rejection reason');
        $order = $this->getDeliveredOrder();
        $result = $order->reject('Bad product');

        self::assertTrue($result);
        self::assertEquals(Order::STATUS_REJECTED, $order->currentStatus());
    }

    public function testRejectClosedOrder()
    {
        $this->setExpectedException(OrderException::class);

        $order = $this->getClosedOrder();
        $order->reject('Bad product');
    }

    public function testRejectPaidOrder()
    {
        $order = $this->getPaidOrder();
        $transaction = $order->reject('Bad product');

        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertEquals(Transaction::TYPE_REFUND, $transaction->type());
        self::assertEquals($order->getPrice(), $transaction->getAmount());
        self::assertSame($order->getUser(), $transaction->getUser());
    }

    private function userWithPositiveBalance()
    {
        $user = new User($clintId = 1, $name = 'name', $address = 'address');
        $user->rechargeBalance(1000);
        
        return $user;
    }

    private function getPaidOrder()
    {
        $order = new Order();
        $order->setPrice(100);
        $order->setUser($this->userWithPositiveBalance());
        $order->pay();

        return $order;
    }

    private function getPaidAndDeliveredOrder()
    {
        $order = $this->getPaidOrder();
        $order->startProgress();
        $order->deliver('Carrier');

        return $order;
    }

    private function getDeliveredOrder()
    {
        $order = new Order();
        $order->startProgress();
        $order->deliver('Carrier');

        return $order;
    }

    private function getClosedOrder()
    {
        $order = $this->getPaidAndDeliveredOrder();
        $order->close();

        return $order;
    }
}
