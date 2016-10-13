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
}
