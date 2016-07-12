<?php

namespace Lunches\Controller;

use Lunches\Model\MenuRepository;
use Doctrine\ORM\EntityManager;
use Lunches\Model\Menu;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class MenusController    
 */
class MenusController extends ControllerAbstract
{
    /** @var EntityManager */
    protected $em;

    /** @var MenuRepository */
    protected $repo;

    /** @var string  */
    protected $productClass;

    /**
     * MenusController constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->productClass = '\Lunches\Model\Menu';
        $this->em = $em;
        $this->repo = $em->getRepository($this->productClass);
    }

    /**
     * @return JsonResponse
     */
    public function getList()
    {
        $menus = $this->repo->getMenus();

        if (0 === count($menus)) {
            return $this->failResponse('Menus not found', 404);
        }

        $menus = array_map(function (Menu $menu) {
            return $menu->toArray();
        }, $menus);

        return $this->successResponse($menus);
    }

    public function getByDateRange(\DateTime $startDate = null, \DateTime $endDate = null)
    {
        $menus = $this->repo->getMenus($startDate, $endDate);

        if (0 === count($menus)) {
            return $this->failResponse('Menus not found', 404);
        }

        $menus = array_map(function (Menu $menu) {
            return $menu->toArray();
        }, $menus);

        return $this->successResponse($menus);
    }

    public function getToday()
    {
        return $this->getByDateRange($today = new \DateTime());
    }
    public function getTomorrow()
    {
        return $this->getByDateRange(new \DateTime('tomorrow'));
    }
    public function getOnCurrentWeek()
    {
        $startDay = new \DateTime('monday this week');
        $endDay = new \DateTime('friday this week');

        return $this->getByDateRange($startDay, $endDay);
    }
    public function getOnNextWeek()
    {
        $startDay = new \DateTime('monday next week');
        $endDay = new \DateTime('friday next week');


        return $this->getByDateRange($startDay, $endDay);
    }
}
