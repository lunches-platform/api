<?php

namespace Lunches\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Entity(repositoryClass="Lunches\Model\ProductRepository")
 * @Table(name="size_weight")
 */
class SizeWeight
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
     * @Column(type="string", length=50, nullable=false)
     */
    protected $size;

    /**
     * @var int
     * @Column(type="integer", nullable=false)
     */
    protected $weight;

    /**
     * @var Menu
     * @ManyToOne(targetEntity="Product")
     */
    protected $product;

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'size' => $this->size,
            'weight' => $this->weight,
            'product' => $this->product,
        ];
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
     */
    public function setSize($size)
    {
        // TODO валидация на enum (big,small,medium)
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     */
    public function setWeight($weight)
    {
        $this->weight = (int) $weight;
    }

    /**
     * @return Menu
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Menu $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

}
