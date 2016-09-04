<?php

namespace Lunches\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embedded;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;
use Lunches\Exception\OrderException;
use Lunches\Exception\ValidationException;

/**
 * @Entity(repositoryClass="Lunches\Model\OrderRepository")
 * @Table(name="`order`")
 */
class Order
{
    const STATUS_CREATED = 'created';
    const STATUS_IN_PROGRESS = 'inProgress';
    const STATUS_CANCELED = 'canceled';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CLOSED = 'closed';

    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;
    /**
     * Number of order. Starts from 1000
     *
     * @var string
     * @Column(type="string", name="order_number", nullable=false)
     */
    protected $orderNumber;
    /**
     * @var User
     * @ManyToOne(targetEntity="User")
     */
    protected $user;
    /**
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $address;
    /**
     * @var CreatedOrder
     * @Embedded(class="CreatedOrder", columnPrefix="created_")
     */
    protected $createdOrder;
    /**
     * @var CanceledOrder
     * @Embedded(class="CanceledOrder", columnPrefix="canceled_")
     */
    protected $canceledOrder;
    /**
     * @var RejectedOrder
     * @Embedded(class="RejectedOrder", columnPrefix="rejected_")
     */
    protected $rejectedOrder;
    /**
     * @var DeliveredOrder
     * @Embedded(class="DeliveredOrder", columnPrefix="delivered_")
     */
    protected $deliveredOrder;
    /**
     * @var string
     * @Column(type="string")
     */
    protected $status;
    /**
     * @var \DateTime
     * @Column(type="date", name="shipment_date")
     */
    protected $shipmentDate;
    /**
     * @var float $price
     *
     * @Column(type="float")
     */
    private $price = 0;
    /**
     * @var OrderPayment
     * @Embedded(class="OrderPayment", columnPrefix="payment_")
     */
    private $payment;
    /**
     * @var LineItem[]
     * @OneToMany(targetEntity="LineItem", mappedBy="order", cascade={"persist"})
     */
    protected $lineItems = [];

    /**
     * Order constructor.
     */
    public function __construct()
    {
        $this->lineItems = new ArrayCollection();
        $this->orderCreated();
        $this->payment = new OrderPayment($this);
    }
    public function changeAddress($address)
    {
        if ($this->status !== self::STATUS_CREATED) {
            throw OrderException::updateFailed('can not change address as of status of Order is not "created"');
        }
        $this->setAddress($address);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $lineItems = [];
        foreach ($this->getLineItems() as $lineItem) {
             $lineItems[] = $lineItem->toArray();
        }

        return [
            'id' => $this->id,
            'price' => $this->price,
            'orderNumber' => $this->orderNumber,
            'user' => $this->user instanceof User ? $this->user->toArray() : null,
            'shipmentDate' => $this->shipmentDate instanceof \DateTime ? $this->shipmentDate->format('Y-m-d') : null,
            'address' => $this->address,
            'items' => $lineItems,
            'status' => $this->status,
            'paid' => $this->getPayment()->isPaid(),
            'created' => $this->createdOrder instanceof CreatedOrder ? $this->createdOrder->toArray() : null,
            'canceled' => $this->canceledOrder instanceof CanceledOrder ? $this->canceledOrder->toArray() : null,
            'rejected' => $this->rejectedOrder instanceof RejectedOrder ? $this->rejectedOrder->toArray() : null,
            'delivered' => $this->deliveredOrder instanceof DeliveredOrder ? $this->deliveredOrder->toArray() : null,
        ];
    }

    /**
     * @return bool|Transaction
     * @throws OrderException
     */
    public function pay()
    {
        if ($this->status === self::STATUS_CANCELED || $this->status === self::STATUS_REJECTED) {
            throw OrderException::canNotPay('Canceled or Rejected orders can not be paid');
        }
        if ($this->status === self::STATUS_CLOSED) {
            return true;
        }
        return $this->getPayment()->pay();
    }

    public function addLineItem(LineItem $lineItem)
    {
        $this->lineItems[] = $lineItem;
        $lineItem->setOrder($this);
    }

    /**
     * TODO refactoring idea:
     * TODO create OrderStatus interface and $this->status will hold all status info instead of two properties
     */
    private function orderCreated()
    {
        $this->createdOrder = new CreatedOrder($this, new \DateTime());
        $this->status = self::STATUS_CREATED;
    }

    public function startProgress()
    {
        if ($this->status !== self::STATUS_CREATED) {
            throw OrderException::failedToChangeStatus('Just "created" orders can became in progress');
        }
        $this->status = self::STATUS_IN_PROGRESS;
    }

    public function delivered($carrier)
    {
        if ($this->status !== self::STATUS_IN_PROGRESS) {
            throw OrderException::failedToChangeStatus('Only "in progress" orders can become "delivered"');
        }
        $this->deliveredOrder = new DeliveredOrder($this, new \DateTime(), $carrier);
        $this->status = self::STATUS_DELIVERED;
    }

    public function close()
    {
        if (!$this->getPayment()->isPaid() || $this->status !== self::STATUS_DELIVERED) {
            throw OrderException::failedToChangeStatus('To close Order it should be paid and delivered');
        }
        $this->status = self::STATUS_CLOSED;
    }

    /**
     * @param string $reason
     * @return Transaction|bool
     * @throws OrderException
     * @throws \Lunches\Exception\ValidationException
     */
    public function cancel($reason = '')
    {
        if ($this->status !== self::STATUS_CREATED) {
            throw OrderException::failedToChangeStatus('Just "created" orders can be canceled');
        }

        $this->canceledOrder = new CanceledOrder($this, new \DateTime(), $reason);
        $this->status = self::STATUS_CANCELED;

        if ($this->getPayment()->isPaid()) {
            return new Transaction(Transaction::TYPE_REFUND, $this->price, $this->user);
        }

        return true;
    }

    public function reject($reason)
    {
        $this->disallowClosed();
        if ($this->status === self::STATUS_REJECTED) {
            throw OrderException::failedToChangeStatus('Such order has been already rejected');
        }
        $this->rejectedOrder = new RejectedOrder($this, new \DateTime(), $reason);
        $this->status = self::STATUS_REJECTED;

        if ($this->getPayment()->isPaid()) {
            return new Transaction(Transaction::TYPE_REFUND, $this->price, $this->user);
        }

        return true;
    }

    public function currentStatus()
    {
        return $this->status;
    }

    private function disallowClosed()
    {
        if ($this->status === self::STATUS_CLOSED) {
            throw OrderException::failedToChangeStatus('Order is closed, action can\'t be performed');
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getLineItems()
    {
        return $this->lineItems;
    }

    /**
     * @param LineItem[] $lineItems
     */
    public function setLineItems($lineItems)
    {
        $this->lineItems = $lineItems;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = (float) $price;
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param string $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * @return \DateTime
     */
    public function getShipmentDate()
    {
        return $this->shipmentDate;
    }

    /**
     * @param \DateTime $shipmentDate
     * TODO remove all methods-accessors
     */
    public function setShipmentDate($shipmentDate)
    {
        $this->shipmentDate = $shipmentDate;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @throws ValidationException
     * TODO make it private
     */
    public function setAddress($address)
    {
        $len = mb_strlen($address);
        if ($len < 1 || $len > 150) {
            // TODO use OrderException
            throw ValidationException::invalidOrder('address must be greater than zero and less than 150 characters');
        }
        $this->address = $address;
    }

    /**
     * @return OrderPayment
     */
    private function getPayment()
    {
        $this->payment->setOrder($this);

        return $this->payment;
    }
}
