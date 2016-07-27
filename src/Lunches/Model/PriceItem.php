<?php

namespace Lunches\Model;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
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

    public function __construct(Price $price, Product $product, $size)
    {
        $this->id = Uuid::uuid4();
        $this->price = $price;
        $this->product = $product;
        $this->size = $size;
    }
}