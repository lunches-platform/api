<?php

namespace AppBundle\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use AppBundle\Exception\ValidationException;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;


/**
 * Class PriceItem
 * @Entity
 * @Table(name="price_item")
 * @SWG\Definition(required={"dishId","size"}, type="object")
 */
class PriceItem implements \JsonSerializable
{
    /**
     * @var Uuid
     *
     * @Id
     * @Column(type="guid")
     * @SWG\Property(readOnly=true)
     */
    protected $id;

    /**
     * @var Price
     * @ManyToOne(targetEntity="Price", inversedBy="items")
     */
    protected $price;

    /**
     * @var Dish
     * @ManyToOne(targetEntity="Dish")
     * @SWG\Property(property="dishId", type="string", description="ID of the dish which is a part of price")
     */
    protected $dish;

    /**
     * Size of portion of the dish
     *
     * @var string
     * @Column(type="string")
     * @SWG\Property(enum={"medium","big"})
     */
    protected $size;


    public function equalsTo(PriceItem $priceItem)
    {
        if ($this->size !== $priceItem->getSize()) {
            return false;
        }

        return $this->dish->getId() === $priceItem->getDish()->getId();
    }

    public function __construct(Price $price, Dish $dish, $size)
    {
        $this->id = Uuid::uuid4();
        $this->price = $price;
        $this->dish = $dish;
        $this->setSize($size);
    }

    public function jsonSerialize()
    {
        return [
            'size' => $this->size,
            'productId' => $this->getDish()->getId(),
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
     * TODO rename getters
     * @return Dish
     */
    public function getDish()
    {
        return $this->dish;
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
        if (!in_array($size, Dish::$availableSizes, true)) {
            throw ValidationException::invalidSize();
        }
        $this->size = $size;
    }
}