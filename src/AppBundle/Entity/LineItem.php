<?php

namespace AppBundle\Entity;

use AppBundle\Exception\ValidationException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;
use Swagger\Annotations as SWG;

/**
 * @Entity(repositoryClass="AppBundle\Entity\LineItemRepository")
 * @Table(name="line_item")
 * @SWG\Definition(required={"size","product"})
 */
class LineItem implements \JsonSerializable
{
    /**
     * @var int
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     * @SWG\Property()
     */
    protected $id;

    /**
     * Size of the dish portion
     *
     * @var string
     * @Column(type="string")
     * @SWG\Property(enum={"small", "medium","big"})
     */
    protected $size;

    /**
     * @var Dish
     * @ManyToOne(targetEntity="Dish")
     * @SWG\Property(ref="#/definitions/Dish")
     */
    protected $dish;

    /**
     * @var Order
     * @ManyToOne(targetEntity="Order")
     * @SWG\Property(ref="#/definitions/Order")
     */
    protected $order;

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'dish' => $this->dish,
            'size' => $this->size,
        ];
    }

    /**
     * @return Dish
     */
    public function getDish()
    {
        return $this->dish;
    }

    /**
     * @param Dish $dish
     */
    public function setDish(Dish $dish)
    {
        $this->dish = $dish;
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
        if (!in_array($size, Dish::$availableSizes, true)) {
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
