<?php

namespace Lunches\Controller;

use Lunches\Exception\ValidationException;
use Lunches\Model\DateRange;
use Lunches\Model\MenuRepository;
use Doctrine\ORM\EntityManager;
use Lunches\Model\Menu;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function getList(Request $request)
    {
        try {
            $range = new DateRange($request->get('startDate'), $request->get('endDate'));
        } catch (ValidationException $e) {
            return $this->failResponse($e->getMessage(), 400);
        }
        if ($range->getStart() < new \DateTime('-2 week')) {
            return $this->failResponse('Can not access menu older than two weeks ago', 400);
        }

        return $this->getByDateRange($range->getStart(), $range->getEnd());
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

    public function getConcrete($date)
    {
        try {
            $concrete = new \DateTime($date);
        } catch (\Exception $e) {
            return $this->failResponse('Invalid date provided', 400);
        }
        return $this->getByDateRange($concrete);
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
