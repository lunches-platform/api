<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Dish;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class DishesController
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
     * DishesController constructor.
     * @param Registry $doctrine
     * @param string $accessToken
     */
    public function __construct(Registry $doctrine, $accessToken)
    {
        $this->doctrine = $doctrine;
        $this->accessToken =$accessToken;
    }

    /**
     * @SWG\Get(
     *     path="/dishes/{dish}", tags={"Dishes"}, operationId="getDishAction",
     *     summary="Retrieve dish details", description="Retrieves the details of an existing dish",
     *     @SWG\Parameter(ref="#/parameters/dishId"),
     *     @SWG\Response(response=200, description="Dish", @SWG\Schema(ref="#/definitions/Dish")),
     * )
     * @param Dish $dish
     * @return Dish
     * @View
     */
    public function getDishAction(Dish $dish)
    {
        return $dish;
    }

    /**
     * @SWG\Get(
     *     path="/dishes", tags={"Dishes"}, operationId="getDishesAction",
     *     summary="Retrieve the list of dishes", description="Return list of dishes using filters",
     *     @SWG\Response(response=200, description="Array of Dish objects", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Dish"))),
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \LogicException
     * @View
     */
    public function getDishesAction()
    {
        $repo = $this->doctrine->getRepository('AppBundle:Dish');

        return $repo->findList();
    }
    /**
     * @SWG\Post(
     *     path="/dishes", tags={"Dishes"}, operationId="postDishesAction",
     *     summary="Creates new Dish", description="Creates new Dish",
     *     @SWG\Parameter(
     *         name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/Dish"),
     *         description="Include here payload in Dish representation",
     *     ),
     *     @SWG\Parameter(ref="#/parameters/accessToken"),
     *     @SWG\Response(response=201, description="Newly created dish", @SWG\Schema(ref="#/definitions/Dish") ),
     * )
     * @RequestParam(name="name")
     * @RequestParam(name="type", requirements="(meat|fish|garnish|salad)")
     * @QueryParam(name="accessToken", description="Access token")
     * @param ParamFetcher $params
     * @return Dish
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View(statusCode=201);
     */
    public function postDishesAction(ParamFetcher $params)
    {
        $this->assertAccessGranted($params);
        $repo = $this->doctrine->getRepository('AppBundle:Dish');

        $existentDish = $repo->findOneBy(['name' => $params->get('name')]);
        if ($existentDish instanceof Dish) {
            throw new BadRequestHttpException('Such dish is already exists');
        }
        $dish = new Dish(Uuid::uuid4());
        $dish->setName($params->get('name'));
        $dish->setType($params->get('type'));

        $em = $this->doctrine->getManager();
        $em->persist($dish);
        $em->flush();

        return $dish;
    }
    private function assertAccessGranted(ParamFetcher $params)
    {
        if ($params->get('accessToken') !== $this->accessToken) {
            throw new AccessDeniedHttpException('Access token is not valid');
        }
    }
}
