<?php


namespace Tests\AppBundle\Entity;


use AppBundle\Entity\Transaction;
use AppBundle\Entity\User;
use AppBundle\Exception\ValidationException;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $user = $this->getValidUser();
        $transaction = new Transaction(Transaction::TYPE_INCOME, 100, $user);

        self::assertInstanceOf(Transaction::class, $transaction);
        self::assertNotEmpty($transaction->type());
        self::assertNotEmpty($transaction->getAmount());
        self::assertNotEmpty($transaction->getUser());
    }
    public function testEmptyType()
    {
        $this->setExpectedException(ValidationException::class, 'Type of transaction is required.');
        new Transaction('', 100, $this->getValidUser());
    }
    public function testInvalidType()
    {
        $this->setExpectedException(ValidationException::class, 'Only "income", "outcome" or "refund" transaction type is allowed');
        new Transaction('invalid type', 100, $this->getValidUser());
    }
    public function testNegativeAmount()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid Transaction. Amount of transaction can not be negative or zero');
        new Transaction(Transaction::TYPE_OUTCOME, -100, $this->getValidUser());
    }

    public function testZeroAmount()
    {
        $this->setExpectedException(ValidationException::class, 'Invalid Transaction. Amount of transaction can not be negative or zero');
        new Transaction(Transaction::TYPE_OUTCOME, 0, $this->getValidUser());
    }

    public function testHugeAmount()
    {
        $this->setExpectedException(ValidationException::class, 'Transaction amount can not be higher than 100 000.00');
        new Transaction(Transaction::TYPE_OUTCOME, 100000000000, $this->getValidUser());
    }

    public function testUpdatePaymentDate()
    {
        $transaction = new Transaction(Transaction::TYPE_INCOME, 100, $this->getValidUser());
        $transaction->paidAt($date = new \DateTimeImmutable());
        self::assertEquals($date, $transaction->paymentDate());
    }

    public function testJsonSerialize()
    {
        $transaction = new Transaction(Transaction::TYPE_INCOME, 100, $this->getValidUser());
        $transactionArr = json_decode(json_encode($transaction), true);
        self::assertTrue(is_array($transactionArr));
        self::assertArrayHasKey('id', $transactionArr);
        self::assertArrayHasKey('type', $transactionArr);
        self::assertArrayHasKey('amount', $transactionArr);
        self::assertArrayHasKey('created', $transactionArr);
    }

    public function testIncomeShouldRechargeUserBalance()
    {
        $user = $this->getValidUser();
        $oldBalance = $user->getBalance();

        new Transaction(Transaction::TYPE_INCOME, $income = 100, $user);
        self::assertEquals($oldBalance + $income, $user->getBalance());
    }
    public function testRefundShouldRechargeUserBalance()
    {
        $user = $this->getValidUser();
        $oldBalance = $user->getBalance();

        new Transaction(Transaction::TYPE_REFUND, $refund = 100, $user);
        self::assertEquals($oldBalance + $refund, $user->getBalance());
    }
    public function testOutcomeShouldChargeUserBalance()
    {
        $user = $this->getValidUser();
        $user->rechargeBalance(100); // set initial balance
        $oldBalance = $user->getBalance();

        new Transaction(Transaction::TYPE_OUTCOME, $outcome = 50, $user);
        self::assertEquals($oldBalance - $outcome, $user->getBalance());
    }

    private function getValidUser()
    {
        return new User($clintId = 1, $name = 'John', 'New York');
    }

}
