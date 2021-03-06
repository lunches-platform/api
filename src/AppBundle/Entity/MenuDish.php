<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Swagger\Annotations as SWG;
use Webmozart\Assert\Assert;

/**
 * Class MenuDish.
 * @Entity(repositoryClass="AppBundle\Entity\MenuDishRepository")
 * @Table(name="menu_dish")
 * @SWG\Definition(required={"menu","dish"}, type="object")
 */
class MenuDish
{
    /**
     * @var int
     * @Column(type="integer") 
     * @Id 
     * @GeneratedValue
     * @SWG\Property()
     */
    protected $id;

    /**
     * @var Menu
     * @ManyToOne(targetEntity="Menu", inversedBy="menuDishes")
     * @SWG\Property
     */
    protected $menu;

    /**
     * @var Dish
     * @ManyToOne(targetEntity="Dish")
     * @SWG\Property
     */
    protected $dish;

    /**
     * @var integer
     * @Column(type="integer", name="position")
     * @SWG\Property
     */
    protected $position;

    public function __construct(Menu $menu, Dish $dish, $position)
    {
        $this->menu = $menu;
        $this->dish = $dish;

        Assert::greaterThanEq($position, 0);
        $this->position = (int) $position;
    }

    public function sameDish(Dish $dish)
    {
        return $this->dish->getId() === $dish->getId();
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
     * @return Dish
     */
    public function getDish()
    {
        return $this->dish;
    }

    /**
     * @param Dish $dish
     */
    public function setDish($dish)
    {
        $this->dish = $dish;
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