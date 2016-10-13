<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Dish;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations\View;
use Swagger\Annotations as SWG;

class DishesController
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
     *     tags={"dish"},
     *     path="/dishes/{dish}",
     *     description="Get Dish by ID",
     *     operationId="getDishAction",
     *     @SWG\Parameter(
     *         description="ID of dish", type="string", in="path", name="dishId", required=true,
     *     ),
     *     @SWG\Response(
     *         response=200, description="Dish",
     *         @SWG\Schema(ref="#/definitions/Dish")
     *     ),
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
     *     tags={"dish"},
     *     path="/dishes",
     *     description="Return list of dishes using filters",
     *     operationId="getDishesAction",
     *     @SWG\Response(response=200, description="List of Dishes", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Dish"))),
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
}
