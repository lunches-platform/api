<?php

namespace AppBundle\Controller;

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
