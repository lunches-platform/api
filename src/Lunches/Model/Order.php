<?php

namespace Lunches\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Entity(repositoryClass="Lunches\Model\OrderRepository")
 * @Table(name="`order`")
 */
class Order
{
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
     * @Column(type="string", nullable=false)
     */
    protected $number;

    /**
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $customer;

    /**
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $address;

    /**
     * @var \DateTime $createdAt
     *
     * @Gedmo\Timestampable(on="create")
     * @Column(type="datetime")
     */
    private $createdAt;

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
     * @var LineItem[]
     * @OneToMany(targetEntity="LineItem", mappedBy="order", cascade={"persist"})
     */
    protected $lineItems;

    /**
     * Order constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->lineItems = new ArrayCollection();
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
            'number' => $this->number,
            'customer' => $this->customer,
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'shipmentDate' => $this->shipmentDate->format('Y-m-d'),
            'lineItems' => $lineItems
        ];
    }

    public function addLineItem(LineItem $lineItem)
    {
        $this->lineItems[] = $lineItem;
        $lineItem->setOrder($this);
        $this->price += $lineItem->getPrice();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreatedAt($created)
    {
        $this->createdAt = $created;
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
     * @return string
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param string $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return mixed
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     */
    public function setNumber($number)
    {
        $this->number = $number;
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
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

}
