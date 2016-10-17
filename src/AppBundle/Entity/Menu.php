<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Swagger\Annotations as SWG;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Entity(repositoryClass="AppBundle\Entity\MenuRepository")
 * @Table(name="menu")
 * @SWG\Definition(required={"id","dishes","type", "date"}, type="object")
 */
class Menu implements \JsonSerializable
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
     * @var MenuDish[]
     * @OneToMany(targetEntity="MenuDish", mappedBy="menu")
     * @SWG\Property
     */
    protected $menuDishes;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @SWG\Property()
     */
    protected $type;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="date")
     * @SWG\Property()
     */
    private $date;

    /**
     * Menu constructor.
     */
    public function __construct()
    {
        $this->menuDishes = new MenuDishes();
    }

    /**
     * @param MenuDish $menuDish
     */
    public function addDish(MenuDish $menuDish)
    {
        $this->menuDishes[] = $menuDish;
    }
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'date' => $this->date->format('Y-m-d'),
            'type' => $this->type,
//            'created' => $this->created->format('Y-m-d H:i:s'),
            'products' => $this->getMenuDishes()->sort(),
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
     * @return MenuDishes
     */
    public function getMenuDishes()
    {
        return $this->menuDishes instanceof MenuDishes ? $this->menuDishes : new MenuDishes($this->menuDishes->getValues());
    }

    /**
     * @param MenuDish[] $menuDishes
     */
    public function setMenuDishes($menuDishes)
    {
        $this->menuDishes = $menuDishes;
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

    public function hasDish(Dish $dish)
    {
        return $this->getMenuDishes()->hasDish($dish);
    }
}
