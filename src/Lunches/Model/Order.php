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
    const STATUS_DELIVERED = 'delivered';

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
            'user' => $this->user->toArray(),
            'shipmentDate' => $this->shipmentDate->format('Y-m-d'),
            'address' => $this->address,
            'items' => $lineItems,
            'status' => $this->status,
            'paid' => $this->payment->isPaid(),
            'created' => $this->createdOrder instanceof CreatedOrder ? $this->createdOrder->toArray() : null,
            'canceled' => $this->canceledOrder instanceof CanceledOrder ? $this->canceledOrder->toArray() : null,
            'delivered' => $this->deliveredOrder instanceof DeliveredOrder ? $this->deliveredOrder->toArray() : null,
        ];
    }

    public function pay()
    {
        $this->payment->setOrder($this);
        return $this->payment->pay();
    }

    public function addLineItem(LineItem $lineItem)
    {
        $this->lineItems[] = $lineItem;
        $lineItem->setOrder($this);
    }

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
        $this->deliveredOrder = new DeliveredOrder($this, new \DateTime(), $carrier);
        $this->status = self::STATUS_DELIVERED;
    }

    /**
     * @param string $reason
     * @return Transaction
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

        if ($this->payment->isPaid()) {
            return new Transaction(Transaction::TYPE_INCOME, $this->price, $this->user);
        }

        return true;
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
     */
    public function setAddress($address)
    {
        $len = mb_strlen($address);
        if ($len < 1 || $len > 150) {
            throw ValidationException::invalidOrder('address must be greater than zero and less than 150 characters');
        }
        $this->address = $address;
    }
}
