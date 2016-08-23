<?php

namespace Lunches\Model;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Lunches\Exception\ValidationException;
use Ramsey\Uuid\Uuid;


/**
 * Class PriceItem
 * @Entity
 * @Table(name="price_item")
 */
class PriceItem
{
    /**
     * @var Uuid
     *
     * @Id
     * @Column(type="guid")
     */
    protected $id;

    /**
     * @var Price
     * @ManyToOne(targetEntity="Price", inversedBy="items")
     */
    protected $price;

    /**
     * @var Product
     * @ManyToOne(targetEntity="Product")
     */
    protected $product;

    /**
     * @var string
     * @Column(type="string")
     */
    protected $size;


    public function equalsTo(PriceItem $priceItem)
    {
        if ($this->size !== $priceItem->getSize()) {
            return false;
        }

        return $this->product->getId() === $priceItem->getProduct()->getId();
    }

    public function __construct(Price $price, Product $product, $size)
    {
        $this->id = Uuid::uuid4();
        $this->price = $price;
        $this->product = $product;
        $this->setSize($size);
    }

    public function toArray()
    {
        return [
            'size' => $this->size,
            'productId' => $this->getProduct()->getId(),
        ];
    }

    /**
     * @return Price
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    private function setSize($size)
    {
        if (!in_array($size, Product::$availableSizes, true)) {
            throw ValidationException::invalidSize();
        }
        $this->size = $size;
    }
}