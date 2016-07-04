<?php

namespace Lunches\Controller;

use Lunches\Model\IngredientRepository;
use Lunches\Model\Ingredients;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class IngredientsController
 */
class IngredientsController extends ControllerAbstract
{
    /** @var EntityManager */
    protected $em;

    /** @var IngredientRepository */
    protected $repo;

    /** @var string  */
    protected $ingredientClass;

    /**
     * IngredientsController constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->ingredientClass = '\Lunches\Model\Ingredient';
        $this->em = $em;
        $this->repo = $em->getRepository($this->ingredientClass);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getList(Request $request)
    {
        $productId = $request->get('productId');
        /** @var Ingredients $ingredients */
        $ingredients = $this->repo->getIngredients([
            'productId' => $productId
        ]);

        if (0 === count($ingredients)) {
            return $this->failResponse('Ingredients not found', 404);
        }
        return $this->successResponse($ingredients->toArray());
    }
}
