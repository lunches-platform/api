<?php

namespace Lunches\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Entity(repositoryClass="Lunches\Model\LineItemRepository")
 * @Table(name="line_item")
 */
class LineItem
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     * @Column(type="float")
     */
    protected $price;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $size;

    /**
     * @var int
     * @Column(type="integer")
     */
    protected $quantity;

    /**
     * @var Product
     * @ManyToOne(targetEntity="Product")
     */
    protected $product;

    /**
     * @var Order
     * @ManyToOne(targetEntity="Order")
     */
    protected $order;

    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $date;

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'productId' => $this->product->getId(),
            'date' => $this->date,
            'size' => $this->size,
            'quantity' => $this->quantity,
        ];
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct(Product $product)
    {
        $this->product = $product;
        $this->recalculatePrice();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    protected function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param string $size
     */
    public function setSize($size)
    {
        $this->recalculatePrice();
        $this->size = $size;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param Order $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity ?: 1;
    }

    /**
     * @param int $quantity
     */
    public function setQuantity($quantity)
    {
        $this->recalculatePrice();
        $this->quantity = $quantity;
    }

    private function recalculatePrice()
    {
        $pricePer100 = $this->product->getPricePer100();
        $this->setPrice($pricePer100 * $this->quantity);
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
    }
}
