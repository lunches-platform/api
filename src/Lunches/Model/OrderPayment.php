<?php

namespace Lunches\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Lunches\Exception\UserException;

/** @Embeddable */
class OrderPayment
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var \DateTime
     * @Column(type="datetime", nullable=false, name="started_at")
     */
    protected $startedAt;
    /**
     * @var \DateTime
     * @Column(type="datetime", nullable=true, name="paid_at")
     */
    protected $paidAt;
    /**
     * @var bool
     * @Column(type="boolean")
     */
    protected $status = false;

    /** @var string */
    protected $error;

    public function __construct(Order $order)
    {
        $this->order = $order;
        $this->startedAt = new \DateTime();
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

        try {
            $transaction = new Transaction(Transaction::TYPE_OUTCOME, $this->order->getPrice(), $this->order->getUser());
            $this->status = true;
            $this->paidAt = new \DateTime();
        } catch (UserException $e) {
            $this->addError($e->getMessage());
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

    private function addError($error)
    {
        $this->error = $error;
    }

    /**
     * Doctrine will not run original constructor for embeddables, so use this temporary hack
     * @param Order $order
     */
    public function setOrder(Order $order)
    {
        $this->order = $order;
    }
}