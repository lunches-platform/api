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
     * @Column(type="string")
     */
    protected $size;

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
            'product' => $this->product->toArray(),
            'size' => $this->size,
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
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @throws ValidationException
     */
    public function setSize($size)
    {
        if (!in_array($size, Product::$availableSizes, true)) {
            throw ValidationException::invalidSize();
        }
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
}
