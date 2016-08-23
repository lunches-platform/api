<?php

namespace Lunches\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Entity(repositoryClass="Lunches\Model\ProductRepository")
 * @Table(name="product", indexes={
 *     @Index(name="created", columns={"created"})
 * })
 */
class Product
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
     * @Column(type="string", length=255, nullable=false)
     */
    protected $name;

    /**
     * Allowed three values: meat, garnish, salad
     *
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    protected $type;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @Column(type="datetime")
     */
    private $updated;

    /**
     * @var ProductImage[]
     * @OneToMany(targetEntity="ProductImage", mappedBy="product", cascade={"persist"})
     */
    protected $images;

    /**
     * @var Ingredient[]
     * @OneToMany(targetEntity="Ingredient", mappedBy="product", cascade={"persist"})
     */
    protected $ingredients;

    const SIZE_SMALL = 'small';
    const SIZE_MEDIUM = 'medium';
    const SIZE_BIG = 'big';

    public static $availableSizes = [
        self::SIZE_SMALL,
        self::SIZE_MEDIUM,
        self::SIZE_BIG,
    ];

    /**
     * Product constructor.
     */
    public function __construct()
    {
        $this->ingredients = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $ingredients = [];
        foreach ($this->getIngredients() as $ingredient) {
             $ingredients[] = $ingredient->getName();
        }

        $images = [];
        foreach ($this->images as $image) {
            /** @var $image ProductImage */
            $images[] = $image->toArray(true);
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'ingredients' => $ingredients,
            'images' => $images,
        ];
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return Ingredient[] 
     */
    public function getIngredients()
    {
        return $this->ingredients;
    }

    /**
     * @param Ingredient[] $ingredients
     */
    public function setIngredients($ingredients)
    {
        $this->ingredients = $ingredients;
    }

    public function addImage(ProductImage $image)
    {
        $this->images[] = $image;
        $image->setProduct($this);
    }

    public function hasImage(ProductImage $productImage)
    {
        foreach ($this->images as $image) {
            if ($image->getImage()->getId() === $productImage->getImage()->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return ProductImage[]
     */
    public function getImages()
    {
        return $this->images;
    }
}
