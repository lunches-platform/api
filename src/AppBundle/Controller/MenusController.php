<?php

namespace AppBundle\Controller;

use AppBundle\Exception\ValidationException;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use AppBundle\Entity\Menu;
use AppBundle\ValueObject\DateRange;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * Class MenusController    
 */
class MenusController
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * DishesController constructor.
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @SWG\Get(
     *     path="/menus",
     *     description="Get list of menus by filters",
     *     operationId="getMenusAction",
     *     @SWG\Parameter(
     *         description="Get menus which greater than start date",
     *         type="string",
     *         in="query",
     *         name="startDate",
     *     ),
     *     @SWG\Parameter(
     *         description="Get menus which less than end date",
     *         type="string",
     *         in="query",
     *         name="endDate",
     *     ),
     *     @SWG\Response(response=200, description="List of Menus", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Menu"))),
     * )
     * @QueryParam(name="startDate", requirements=@Assert\DateTime(format="Y-m-d"), strict=true)
     * @QueryParam(name="endDate", requirements=@Assert\DateTime(format="Y-m-d"), strict=true)
     * @param ParamFetcher $params
     * @return \AppBundle\Entity\Menu[]
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View
     */
    public function getMenusAction(ParamFetcher $params)
    {
        try {
            $range = new DateRange($params->get('startDate'), $params->get('endDate'));
        } catch (ValidationException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
//        if ($range->getStart() < new \DateTime('-2 week')) {
//            throw new BadRequestHttpException('Can not access menu older than two weeks ago');
//        }

        return $this->getByDateRange($range->getStart(), $range->getEnd());
    }

    /**
     * @SWG\Get(
     *     path="/menus/{concrete}",
     *     description="Get menu for date",
     *     operationId="getMenuAction",
     *     @SWG\Parameter(
     *         description="Menu date", type="date", in="path", name="date", required=true,
     *     ),
     *     @SWG\Response(
     *         response=200, description="Menu",
     *         @SWG\Schema(ref="#/definitions/Menu")
     *     ),
     * )
     * @param \DateTime $concrete
     * @View
     * @return Menu[]
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getMenuAction(\DateTime $concrete)
    {
        return $this->getByDateRange($concrete);
    }

    /**
     * @param \DateTime|null $startDate
     * @param \DateTime|null $endDate
     * @return \AppBundle\Entity\Menu[]
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getByDateRange(\DateTime $startDate = null, \DateTime $endDate = null)
    {
        $repo = $this->doctrine->getRepository('AppBundle:Menu');
        $menus = $repo->getMenus($startDate, $endDate);

        if (0 === count($menus)) {
            throw new NotFoundHttpException('Menus not found');
        }

        return $menus;
    }


    /**
     * @SWG\Get(
     *     path="/menus/today",
     *     description="Get menu for today",
     *     operationId="getTodayMenuAction",
     *     @SWG\Response(response=200, description="Menu", @SWG\Schema(ref="#/definitions/Menu")),
     * )
     * @Get("/menus/today")
     * @View
     * @return Menu[]
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getTodayMenuAction()
    {
        return $this->getByDateRange($today = new \DateTime());
    }
    /**
     * @SWG\Get(
     *     path="/menus/tomorrow",
     *     description="Get menu for tomorrow",
     *     operationId="getTomorrowMenuAction",
     *     @SWG\Response(response=200, description="Menu", @SWG\Schema(ref="#/definitions/Menu")),
     * )
     * @Get("/menus/tomorrow")
     * @View
     * @return Menu[]
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getTomorrowMenuAction()
    {
        return $this->getByDateRange(new \DateTime('tomorrow'));
    }
    /**
     * @SWG\Get(
     *     path="/menus/week/current",
     *     description="Get menu on current week",
     *     operationId="getCurrentWeekMenuAction",
     *     @SWG\Response(response=200, description="Menu", @SWG\Schema(ref="#/definitions/Menu")),
     * )
     * @Get("/menus/week/current")
     * @View
     * @return Menu[]
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getCurrentWeekMenuAction()
    {
        $startDay = new \DateTime('monday this week');
        $endDay = new \DateTime('friday this week');

        return $this->getByDateRange($startDay, $endDay);
    }
    /**
     * @SWG\Get(
     *     path="/menus/week/next",
     *     description="Get next week menu",
     *     operationId="getNextWeekMenuAction",
     *     @SWG\Response(response=200, description="Menu", @SWG\Schema(ref="#/definitions/Menu")),
     * )
     * @Get("/menus/week/next")
     * @View
     * @return Menu[]
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getNextWeekMenuAction()
    {
        $startDay = new \DateTime('monday next week');
        $endDay = new \DateTime('friday next week');


        return $this->getByDateRange($startDay, $endDay);
    }
}
