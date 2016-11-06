<?php

namespace AppBundle\ValueObject;

use AppBundle\Entity\Order;
use AppBundle\Entity\Transaction;
use AppBundle\Entity\User;
use AppBundle\Exception\UserException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

/** @Embeddable */
class OrderPayment
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * Date and time when order payment process was started
     *
     * @var \DateTimeImmutable
     * @Column(type="datetime", nullable=false, name="started_at")
     */
    protected $startedAt;
    /**
     * Date and time when order payment was proceed successfully
     *
     * @var \DateTimeImmutable
     * @Column(type="datetime", nullable=true, name="paid_at")
     */
    protected $paidAt;
    /**
     * Order payment status with boolean value
     *
     * @var bool
     * @Column(type="boolean")
     */
    protected $status = false;

    /**
     * Whether user credited to pay for an order or no
     *
     * @var bool
     * @Column(type="boolean", nullable=true)
     */
    protected $credited = false;

    /** @var string */
    protected $error;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->startedAt = new \DateTimeImmutable();
    }

    public function pay()
    {
        if ($this->isPaid()) {
            $this->addError('Order is paid already');
            return false;
        }
        if (!$this->order->getPrice()) {
            $this->addError('Order price is not valid to pay');
            return false;
        }
        $price = $this->order->getPrice();
        $user = $this->order->getUser();

        try {
            $transaction = new Transaction(Transaction::TYPE_OUTCOME, $price, $user);
            $this->status = true;
            $this->paidAt = new \DateTimeImmutable();
            $user->payCredit($price);
        } catch (UserException $e) {
            $this->addError($e->getMessage());
            $this->takeCredit($user, $price);
            return false;
        }

        return $transaction;
    }

    public function isPaid()
    {
        return $this->status;
    }

    public function getLastError()
    {
        if ($this->error) {
            return 'Order payment error: ' . $this->error;
        }
        return null;
    }

    /**
     * Doctrine will not run original constructor for embeddables, so use this temporary hack
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }

    private function addError($error)
    {
        $this->error = $error;
    }

    private function takeCredit(User $user, $price)
    {
        if ($this->credited === false) {
            $user->takeCredit($price);
            $this->credited = true;
        }
    }
}