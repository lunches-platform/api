<?php

namespace Lunches\Model;


use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * Class MenuProduct.
 * @Entity(repositoryClass="Lunches\Model\MenuProductRepository")
 * @Table(name="menu_product")
 */
class MenuProduct
{
    /**
     * @var int
     * @Column(type="integer") 
     * @Id 
     * @GeneratedValue
     */
    protected $id;

    /**
     * @var Menu
     * @ManyToOne(targetEntity="Menu", inversedBy="menuProducts")
     */
    protected $menu;

    /**
     * @var Product
     * @ManyToOne(targetEntity="Product")
     */
    protected $product;

    /**
     * @var integer
     * @Column(type="integer", name="position")
     */
    protected $position;

    public function sameProduct(Product $product)
    {
        return $this->product->getId() === $product->getId();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Menu
     */
    public function getMenu()
    {
        return $this->menu;
    }

    /**
     * @param Menu $menu
     */
    public function setMenu($menu)
    {
        $this->menu = $menu;
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
    public function setProduct($product)
    {
        $this->product = $product;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }
}