<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Swagger\Annotations as SWG;

/**
 * @ORM\Entity(repositoryClass="DishRepository")
 * @ORM\Table(name="dish", indexes={
 *     @ORM\Index(name="created", columns={"created"})
 * })
 * @SWG\Definition(required={"name","type"}, type="object")
 */
class Dish implements \JsonSerializable
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @SWG\Property()
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=false)
     * @SWG\Property()
     */
    protected $name;

    /**
     * Allowed three values: meat, garnish, salad
     *
     * @var string
     * @ORM\Column(type="string", length=10, nullable=false)
     * @SWG\Property(enum={"meat","garnish","salad","fish"})
     */
    protected $type;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     * @SWG\Property()
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     * @SWG\Property()
     */
    private $updated;

    /**
     * @var DishImage[]
     * @ORM\OneToMany(targetEntity="DishImage", mappedBy="dish", cascade={"persist"})
     * @SWG\Property()
     */
    protected $images;

    /**
     * @var Ingredient[]
     * @ORM\OneToMany(targetEntity="Ingredient", mappedBy="dish", cascade={"persist"})
     * @SWG\Property()
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
     * Dish constructor.
     * @param string|int $id
     */
    public function __construct($id)
    {
        $this->id = $id;
        $this->ingredients = new ArrayCollection();
        $this->images = new ArrayCollection();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $ingredients = [];
        foreach ($this->getIngredients() as $ingredient) {
             $ingredients[] = $ingredient->getName();
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'ingredients' => $ingredients,
            'images' => $this->images,
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

    public function addImage(DishImage $image)
    {
        $this->images[] = $image;
        $image->setDish($this);
    }

    public function hasImage(DishImage $dishImage)
    {
        foreach ($this->images as $image) {
            if ($image->getImage()->getId() === $dishImage->getImage()->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return DishImage[]
     */
    public function getImages()
    {
        return $this->images;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }
}
