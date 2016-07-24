<?php

namespace Lunches\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Lunches\Model\MenuRepository")
 * @Table(name="menu")
 */
class Menu
{
    /**
     * @var int
     * @Id @GeneratedValue @Column(type="integer")
     */
    protected $id;

    /**
     * @var MenuProduct[]
     * @OneToMany(targetEntity="MenuProduct", mappedBy="menu")
     */
    protected $menuProducts;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @Column(type="date")
     */
    private $date;

    /**
     * Menu constructor.
     */
    public function __construct()
    {
        $this->menuProducts = new MenuProducts();
    }

    /**
     * @param MenuProduct $product
     */
    public function addProduct(MenuProduct $product)
    {
        $this->menuProducts[] = $product;
    }
    /**
     * @return array
     */
    public function toArray()
    {
        $products = [];
        foreach ($this->getMenuProducts() as $menuProduct) {
            $products[] = $menuProduct->getProduct()->toArray();
        }

        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
//            'created' => $this->created->format('Y-m-d H:i:s'),
            'products' => $products
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
     * @return MenuProducts
     */
    public function getMenuProducts()
    {
        return $this->menuProducts;
    }

    /**
     * @param MenuProduct[] $menuProducts
     */
    public function setMenuProducts($menuProducts)
    {
        $this->menuProducts = $menuProducts;
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
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @param int $productId
     * @return Product|null
     */
    public function getProductById($productId)
    {
        foreach ($this->getMenuProducts() as $menuProduct) {
            /** @var $menuProduct MenuProduct */
            if ($productId === $menuProduct->getProduct()->getId()) {
                return $menuProduct->getProduct();
            }
        }

        return null;
    }
}
