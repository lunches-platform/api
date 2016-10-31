<?php

namespace AppBundle\Controller;

use AppBundle\MenuFactory;
use AppBundle\Exception\ValidationException;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use AppBundle\Entity\Menu;
use AppBundle\ValueObject\DateRange;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
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
     * @var string
     */
    private $accessToken;
    /**
     * @var MenuFactory
     */
    private $menuFactory;

    /**
     * MenusController constructor.
     *
     * @param Registry $doctrine
     * @param MenuFactory $menuFactory
     * @param string $accessToken
     */
    public function __construct(Registry $doctrine, MenuFactory $menuFactory, $accessToken)
    {
        $this->doctrine = $doctrine;
        $this->accessToken  =$accessToken;
        $this->menuFactory = $menuFactory;
    }

    /**
     * @SWG\Get(
     *     path="/menus", tags={"Menus"}, operationId="getMenusAction",
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

        return $this->getByDateRange($range->getStart(), $range->getEnd());
    }

    /**
     * @SWG\Get(
     *     path="/menus/{concrete}", tags={"Menus"}, operationId="getMenuAction",
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
     *     path="/menus/today", tags={"Menus"}, operationId="getTodayMenuAction",
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
     *     path="/menus/tomorrow", tags={"Menus"}, operationId="getTomorrowMenuAction",
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
     *     path="/menus/week/current", tags={"Menus"}, operationId="getCurrentWeekMenuAction",
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
     *     path="/menus/week/next", tags={"Menus"}, operationId="getNextWeekMenuAction",
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
    /**
     * @SWG\Put(
     *     path="/menus/{date}", tags={"Menus"}, operationId="putMenuAction",
     *     summary="Add new menu", description="Adds new menu for specified date",
     *     @SWG\Parameter(
     *         description="Day at which the Menu is available", type="string", format="date", in="path", name="date", required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/Menu"),
     *         description="Include here payload in Menu representation",
     *     ),
     *     @SWG\Response(response=200, description="Recently added menu", @SWG\Schema(ref="#/definitions/Menu") ),
     * )
     * @RequestParam(name="products")
     * @RequestParam(name="type", requirements="(diet|regular)")
     * @QueryParam(name="accessToken", description="Access token")
     * @param \DateTime $date
     * @param ParamFetcher $params
     * @return Menu
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View(statusCode=201);
     */
    public function putMenuAction(\DateTime $date, ParamFetcher $params)
    {
        $this->assertAccessGranted($params);
        try {
            $menu = $this->menuFactory->create($date, (array) $params->get('products'), $params->get('type'));
        } catch (ValidationException $e) {
            throw new BadRequestHttpException('Can not create menu:'.$e->getMessage());
        }
        $em = $this->doctrine->getManager();
        $em->persist($menu);
        $em->flush();

        return $menu;
    }
    private function assertAccessGranted(ParamFetcher $params)
    {
        if ($params->get('accessToken') !== $this->accessToken) {
            throw new AccessDeniedHttpException('Access token is not valid');
        }
    }
}
