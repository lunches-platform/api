<?php

namespace AppBundle\Entity;

use AppBundle\Exception\OrderException;
use AppBundle\Exception\ValidationException;
use AppBundle\ValueObject\CanceledOrder;
use AppBundle\ValueObject\CreatedOrder;
use AppBundle\ValueObject\DeliveredOrder;
use AppBundle\ValueObject\OrderPayment;
use AppBundle\ValueObject\RejectedOrder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Entity\OrderRepository")
 * @ORM\Table(name="`order`")
 * @SWG\Definition(required={"user","address","shipmentDate","lineItems"}, type="object")
 */
class Order implements \JsonSerializable
{
    const STATUS_CREATED = 'created';
    const STATUS_IN_PROGRESS = 'inProgress';
    const STATUS_CANCELED = 'canceled';
    const STATUS_REJECTED = 'rejected';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CLOSED = 'closed';

    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @SWG\Property(readOnly=true)
     */
    protected $id;
    /**
     * Number of order. Starts from 1000. Generated automatically, no need to specify custom order number as it will be ignored
     *
     * @var string
     * @ORM\Column(type="string", name="order_number", nullable=false)
     * @SWG\Property(readOnly=true)
     */
    protected $orderNumber;
    /**
     * User who creates an Order. When you create an order, specify only **userId**
     *
     * @var User
     * @ORM\ManyToOne(targetEntity="User")
     * @SWG\Property(ref="#/definitions/User")
     */
    protected $user;
    /**
     * The shipping address for the order. Can be omitted, in this case user default address will be used
     *
     * @var string
     * @ORM\Column(type="string", nullable=false)
     * @SWG\Property()
     */
    protected $address;
    /**
     * Date and time when order was created
     *
     * @var CreatedOrder
     * @ORM\Embedded(class="AppBundle\ValueObject\CreatedOrder", columnPrefix="created_")
     * @SWG\Property(property="created", ref="#/definitions/CreatedOrder", readOnly=true),
     */
    protected $createdOrder;
    /**
     * @var CanceledOrder
     * @ORM\Embedded(class="AppBundle\ValueObject\CanceledOrder", columnPrefix="canceled_")
     * @SWG\Property(property="canceled", ref="#/definitions/CanceledOrder", readOnly=true)
     */
    protected $canceledOrder;
    /**
     * @var RejectedOrder
     * @ORM\Embedded(class="AppBundle\ValueObject\RejectedOrder", columnPrefix="rejected_")
     * @SWG\Property(property="rejected", ref="#/definitions/RejectedOrder", readOnly=true)
     */
    protected $rejectedOrder;
    /**
     * @var DeliveredOrder
     * @ORM\Embedded(class="AppBundle\ValueObject\DeliveredOrder", columnPrefix="delivered_")
     * @SWG\Property(property="delivered", ref="#/definitions/DeliveredOrder", readOnly=true)
     */
    protected $deliveredOrder;
    /**
     * @var string
     * @ORM\Column(type="string")
     * @SWG\Property(enum={"created","inProgress","canceled","rejected","delivered","closed"}, readOnly=true)
     */
    protected $status;
    /**
     * @var \DateTime
     * @ORM\Column(type="date", name="shipment_date")
     * @SWG\Property()
     */
    protected $shipmentDate;
    /**
     * Order price. Read only. Calculates automatically and based on order line items cost sum, delivery cost and taxes.
     * If user orders all dishes of menu, orders costs lower then ordering of dishes separately
     *
     * @var float
     *
     * @ORM\Column(type="float")
     * @SWG\Property(readOnly=true)
     */
    private $price = 0;
    /**
     * Order payment status with boolean value
     *
     * @var OrderPayment
     * @ORM\Embedded(class="AppBundle\ValueObject\OrderPayment", columnPrefix="payment_")
     * @SWG\Property(property="paid", type="boolean", readOnly=true),
     */
    private $payment;
    /**
     * @var LineItem[]
     * @ORM\OneToMany(targetEntity="LineItem", mappedBy="order", cascade={"persist"})
     * @SWG\Property
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
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'orderNumber' => $this->orderNumber,
            'user' => $this->user,
            'shipmentDate' => $this->shipmentDate instanceof \DateTime ? $this->shipmentDate->format('Y-m-d') : null,
            'address' => $this->address,
            'items' => $this->lineItems,
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

    private function orderCreated()
    {
        $this->createdOrder = new CreatedOrder($this, new \DateTime());
        $this->status = self::STATUS_CREATED;
    }

    public function startProgress()
    {
        $this->assertStatus(self::STATUS_CREATED, 'Just "created" orders can became in progress');
        $this->status = self::STATUS_IN_PROGRESS;
    }

    public function deliver($carrier)
    {
        $this->assertStatus(self::STATUS_IN_PROGRESS, 'Only "in progress" orders can become "delivered"');
        $this->deliveredOrder = new DeliveredOrder($this, new \DateTime(), $carrier);
        $this->status = self::STATUS_DELIVERED;
    }

    public function close()
    {
        $this->assertStatus(self::STATUS_DELIVERED, 'To close Order it should be delivered');

        if (!$this->getPayment()->isPaid()) {
            throw OrderException::failedToChangeStatus('To close Order it should be paid');
        }
        $this->status = self::STATUS_CLOSED;
    }

    /**
     * @param string $reason
     * @return Transaction|bool
     * @throws OrderException
     * @throws \AppBundle\Exception\ValidationException
     */
    public function cancel($reason = '')
    {
        $this->assertStatus(self::STATUS_CREATED, 'Just "created" orders can be canceled');

        $this->canceledOrder = new CanceledOrder($this, new \DateTime(), $reason);

        if ($this->getPayment()->isPaid()) {
            return new Transaction(Transaction::TYPE_REFUND, $this->price, $this->user);
        }
        $this->status = self::STATUS_CANCELED;

        return true;
    }

    public function reject($reason)
    {
        if ($this->status === self::STATUS_CLOSED) {
            throw OrderException::failedToChangeStatus('Order is closed, can\'t reject');
        }
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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return LineItem[]
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
        $this->lineItems->clear();
        array_map([$this, 'addLineItem'], $lineItems);
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

    private function assertStatus($status, $errMsg)
    {
        if ($this->status !== $status) {
            throw OrderException::failedToChangeStatus($errMsg);
        }
    }
}
