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
     * @ManyToOne(targetEntity="Menu")
     */
    protected $menu;

    /**
     * @var Product
     * @ManyToOne(targetEntity="Product")
     */
    protected $product;

    /**
     * @var integer
     * @Column(type="integer", name="order_number")
     */
    protected $orderNumber;

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
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @param int $orderNumber
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }
}