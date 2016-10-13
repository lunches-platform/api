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
     *     path="/menus", tags={"menu"}, operationId="getMenusAction",
     *     summary="List available menus", description="Retrieves list of menus by filters (query params)",
     *     @SWG\Parameter(ref="#/parameters/startDate"),
     *     @SWG\Parameter(ref="#/parameters/endDate"),
     *     @SWG\Response(response=200, description="Array of Menu objects", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Menu"))),
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
        if ($range->getStart() < new \DateTime('-2 week')) {
            throw new BadRequestHttpException('Can not access menu older than two weeks ago');
        }

        return $this->getByDateRange($range->getStart(), $range->getEnd());
    }

    /**
     * @SWG\Get(
     *     path="/menus/{concrete}", tags={"menu"}, operationId="getMenuAction",
     *     summary="Retrieve menus for concrete day", description="Retrieves menus which are available for specified date",
     *     @SWG\Parameter(
     *         description="Menu date", type="string", format="date", in="path", name="date", required=true,
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
     *     path="/menus/today", tags={"menu"}, operationId="getTodayMenuAction",
     *     summary="Retrieve today menus", description="Handy shortcut to list all available dish menus for today",
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
     *     path="/menus/tomorrow", tags={"menu"}, operationId="getTomorrowMenuAction",
     *     summary="Retrieve tomorrow menus ", description="Handy shortcut to list all dish menus which will be available to ship tomorrow",
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
     *     path="/menus/week/current", tags={"menu"}, operationId="getCurrentWeekMenuAction",
     *     summary="Retrieve current week menus", description="Handy shortcut to retrieve all menus which are available this week",
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
     *     path="/menus/week/next", tags={"menu"}, operationId="getNextWeekMenuAction",
     *     summary="Retrieve next week menus", description="Handy shortcut to retrieve all menus which will be available next week",
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
