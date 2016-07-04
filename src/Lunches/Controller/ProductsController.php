<?php

namespace Lunches\Controller;

use Doctrine\ORM\EntityManager;
use Lunches\Model\Product;
use Lunches\Model\ProductRepository;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class ProductsController
 */
class ProductsController extends ControllerAbstract
{
    /** @var EntityManager */
    protected $em;

    /** @var ProductRepository */
    protected $repo;

    /** @var string  */
    protected $productClass;

    /**
     * ProductsController constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->productClass = '\Lunches\Model\Product';
        $this->em = $em;
        $this->repo = $em->getRepository($this->productClass);
    }

    /**
     * @return JsonResponse
     */
    public function getList()
    {
        $products = $this->repo->getProducts();

        if (0 === count($products)) {
            return $this->failResponse('Products not found', 404);
        }

        $products = array_map(function (Product $product) {
            return $product->toArray();
        }, $products);

        return $this->successResponse($products);
    }
}
