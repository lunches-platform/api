<?php

namespace Lunches\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\ManyToMany;
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
     * @var Product[]
     * @ManyToMany(targetEntity="Product")
     * @JoinTable(name="menu_product",
     *      joinColumns={@JoinColumn(name="menu_id", referencedColumnName="id")},
     *      inverseJoinColumns={@JoinColumn(name="product_id", referencedColumnName="id")}
     *      )
     */
    protected $products;

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
        $this->products = new Products();
    }

    /**
     * @param Product $product
     */
    public function addProduct(Product $product)
    {
        $this->products[] = $product;
    }
    /**
     * @return array
     */
    public function toArray()
    {
        $products = [];
        foreach ($this->getProducts() as $product) {
            $products[] = $product->toArray();
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
     * @return Product[]
     */
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * @param string $products
     */
    public function setProducts($products)
    {
        $this->products = $products;
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
}
