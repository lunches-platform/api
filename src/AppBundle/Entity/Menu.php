<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Swagger\Annotations as SWG;
use Gedmo\Mapping\Annotation as Gedmo;
use Webmozart\Assert\Assert;

/**
 * @Entity(repositoryClass="AppBundle\Entity\MenuRepository")
 * @Table(name="menu")
 * @SWG\Definition(required={"products","type", "date"}, type="object")
 */
class Menu implements \JsonSerializable
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @SWG\Property(readOnly=true)
     */
    protected $id;

    /**
     * @var MenuDish[]
     * @OneToMany(targetEntity="MenuDish", mappedBy="menu", cascade={"persist"})
     * @SWG\Property
     */
    protected $menuDishes;

    /**
     * @var string
     * @ORM\Column(type="string")
     * @SWG\Property(enum={"diet","regular"})
     */
    protected $type;

    /**
     * @var \DateTimeImmutable $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="date")
     * @SWG\Property()
     */
    private $date;

    /**
     * Menu constructor.
     *
     * @param \DateTimeImmutable $date
     * @param string $type
     */
    public function __construct(\DateTimeImmutable $date, $type)
    {
        $this->menuDishes = new MenuDishes();
        $this->date = $date;
        Assert::oneOf($type, ['diet', 'regular']);
        $this->type = $type;
    }

    /**
     * @param MenuDish $menuDish
     */
    public function addDish(MenuDish $menuDish)
    {
        if (!$this->hasDish($menuDish->getDish())) {
            $this->menuDishes[] = $menuDish;
        }
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
    public function id()
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

    public function hasDish(Dish $dish)
    {
        return $this->getMenuDishes()->hasDish($dish);
    }
}
