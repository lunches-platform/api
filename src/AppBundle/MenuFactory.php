<?php

namespace AppBundle;

use AppBundle\Entity\DishRepository;
use AppBundle\Entity\Menu;
use AppBundle\Entity\MenuDish;
use AppBundle\Entity\MenuRepository;
use Doctrine\Bundle\DoctrineBundle\Registry;
use AppBundle\Exception\ValidationException;

class MenuFactory
{
    /** @var MenuRepository  */
    protected $menuRepository;

    /** @var DishRepository  */
    protected $dishRepository;

    public function __construct(Registry $doctrine)
    {
        $this->menuRepository = $doctrine->getRepository('AppBundle:Menu');
        $this->dishRepository = $doctrine->getRepository('AppBundle:Dish');
    }

    public function create(\DateTime $date, array $dishesArr, $type)
    {
        if ($this->menuRepository->exists($date, $type)) {
            throw new ValidationException('Such menu exists already');
        }

        $menu = new Menu($date, $type);

        $dishes = array_map([$this, 'createDish'], $dishesArr);
        $dishes = array_filter($dishes);

        if (count($dishes) === 0) {
            throw new ValidationException('No valid dishes provided');
        }

        foreach ($dishes as $position => $dish) {
            $menu->addDish(new MenuDish($menu, $dish, $position));
        }
        return $menu;
    }

    private function createDish($dish)
    {
        $dish = (array) $dish;
        if (!array_key_exists('id', $dish)) {
            return null;
        }
        return $this->dishRepository->get($dish['id']);
    }
}