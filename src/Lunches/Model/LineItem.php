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
use Lunches\Exception\ValidationException;

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
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'price' => $this->price,
            'product' => $this->product->toArray(),
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
     * @throws \Lunches\Exception\ValidationException
     * @throws \Lunches\Exception\RuntimeException
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
     * @throws ValidationException
     */
    protected function setPrice($price)
    {
        if ($price < 0) {
            throw ValidationException::invalidPrice();
        }
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
     * @throws \Lunches\Exception\ValidationException
     * @throws \Lunches\Exception\RuntimeException
     */
    public function setSize($size)
    {
        $this->size = $size;
        $this->recalculatePrice();
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
     * @throws \Lunches\Exception\ValidationException
     * @throws \Lunches\Exception\RuntimeException
     */
    public function setQuantity($quantity)
    {
        $this->quantity = (int) $quantity;
        $this->recalculatePrice();
    }

    /**
     * Recalculate price of LineItem that consists of product quantity and its weight
     * @throws \Lunches\Exception\ValidationException
     * @throws \Lunches\Exception\RuntimeException
     */
    private function recalculatePrice()
    {
        if ($this->product && $this->size && $this->quantity) {
            $pricePer100 = $this->product->getPricePer100();
            $weight = $this->product->getSizeWeightsCollection()->getWeightFromSize($this->size);
            $this->setPrice($pricePer100 / 100 * $weight * $this->quantity);
        }
    }
}
