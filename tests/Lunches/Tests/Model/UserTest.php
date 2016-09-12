<?php


namespace Lunches\Tests\Model;


use Lunches\Exception\UserException;
use Lunches\Exception\ValidationException;
use Lunches\Model\User;

class UserTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $user = $this->getUser();
        self::assertInstanceOf(User::class, $user);
        self::assertEquals(0, $user->getBalance());
    }

    public function testRechargeBalance()
    {
        $user = $this->getUser();
        $user->rechargeBalance($first = 100);

        self::assertEquals($first, $user->getBalance());

        $user->rechargeBalance($second = 50);

        self::assertEquals($first + $second,  $user->getBalance());
    }

    public function testChargeBalance()
    {
        $user = $this->getUser();
        $user->rechargeBalance($initial = 100);

        $user->chargeBalance($first = 50);
        self::assertEquals($initial - $first, $user->getBalance());

        $user->chargeBalance($second = 30);

        self::assertEquals($initial - $first - $second,  $user->getBalance());
    }

    public function testChargeBalanceInsufficientFundsZeroInitial()
    {
        $this->setExpectedException(UserException::class, 'Insufficient funds to charge');
        $user = $this->getUser();
        $user->chargeBalance(100);
    }
    public function testChargeBalanceInsufficientFundsNonZeroInitial()
    {
        $user = $this->getUser();
        $user->rechargeBalance($initial = 100);
        $user->chargeBalance($charge = 50);

        self::assertEquals($initial - $charge, $user->getBalance());
        $this->setExpectedException(UserException::class, 'Insufficient funds to charge');

        $user->chargeBalance(60);
    }

    public function testTakeCredit()
    {
        $user = $this->getUser();
        $user->takeCredit($credit = 100);

        self::assertEquals($credit, $user->currentCredit());
    }

    public function testPayCredit()
    {
        $user = $this->getUser();
        $user->takeCredit($credit = 100);
        $user->payCredit($paid = 50);

        self::assertEquals($credit - $paid, $user->currentCredit());
    }
    public function testPayCreditWhenNoCredit()
    {
        $user = $this->getUser();
        $user->payCredit($paid = 50);

        self::assertEquals(0, $user->currentCredit());
    }

    public function testChangeAddress()
    {
        $user = new User(1, 'Sergey Brin', $address = 'First address');
        self::assertEquals($address, $user->getAddress());

        $user->changeAddress($newAddress = 'Second address');
        self::assertEquals($newAddress, $user->getAddress());
    }

    public function testToArray()
    {
        $user = $this->getUser();
        $userArr = $user->toArray();
        self::assertTrue(is_array($userArr));

        self::assertArrayHasKey('id', $userArr);
        self::assertArrayHasKey('clientId', $userArr);
        self::assertArrayHasKey('fullname', $userArr);
        self::assertArrayHasKey('address', $userArr);
        self::assertArrayHasKey('balance', $userArr);
        self::assertArrayHasKey('credit', $userArr);
    }

    public function testEmptyUsername()
    {
        $this->setExpectedException(ValidationException::class);
        new User(1, '', 'Address');
    }

    public function testUsernameTooShort()
    {
        $this->setExpectedException(ValidationException::class);
        new User(1, 'a', 'Address');
    }
    public function testUsernameTooLong()
    {
        $this->setExpectedException(ValidationException::class);
        new User(1, str_repeat('u', 51) , 'Address');
    }

    public function testEmptyAddress()
    {
        $this->setExpectedException(ValidationException::class);
        new User(1, 'Sergey Brin', $address = '');
    }

    public function testInvalidAddressLength()
    {
        $this->setExpectedException(ValidationException::class);
        new User(1, 'Sergey Brin', $address = str_repeat('a', 151));
    }

    private function getUser()
    {
        return new User('1', 'Sergey Brin', 'Palo Alto');
    }
}
