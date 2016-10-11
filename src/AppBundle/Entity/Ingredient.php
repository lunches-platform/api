<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;
use Swagger\Annotations AS SWG;

/**
 * @Entity(repositoryClass="AppBundle\Entity\IngredientRepository")
 * @Table(name="ingredient", indexes={
 *     @Index(name="created", columns={"created"})
 * })
 * @SWG\Definition(required={"name"})
 */
class Ingredient implements \JsonSerializable
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
     * @var string
     * @Column(type="string", length=50, nullable=false)
     * @SWG\Property()
     */
    protected $name;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @Column(type="datetime")
     * @SWG\Property()
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @Column(type="datetime")
     * @SWG\Property()
     */
    private $updated;

    /**
     * @var Dish
     * @ManyToOne(targetEntity="AppBundle\Entity\Dish")
     */
    protected $dish;

    /**
     * @param array $data
     * @return Ingredient
     */
    public static function factory(array $data)
    {
        $ingredient = new self();

        if (array_key_exists('name', $data)) {
            $ingredient->setName($data['name']);
        }
        if (array_key_exists('created', $data)) {
            $ingredient->setCreated($data['created']);
        } else {
            $ingredient->setCreated(new \DateTime());
        }
        if (array_key_exists('updated', $data)) {
            $ingredient->setUpdated($data['updated']);
        } else {
            $ingredient->setUpdated(new \DateTime());
        }

        return $ingredient;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created' => $this->created->format('Y-m-d H:i:s'),
            'updated' => $this->updated->format('Y-m-d H:i:s'),
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

}
