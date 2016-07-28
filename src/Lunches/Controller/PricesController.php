<?php

namespace Lunches\Controller;

use Doctrine\ORM\EntityManager;
use Lunches\Exception\ValidationException;
use Lunches\Model\Price;
use Lunches\Model\PriceRepository;
use Lunches\Model\SizeWeight;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PricesController
 */
class PricesController extends ControllerAbstract
{
    /** @var EntityManager */
    protected $em;

    /** @var PriceRepository */
    protected $repo;

    /** @var string  */
    protected $priceClass;

    /**
     * ProductsController constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->priceClass = '\Lunches\Model\Price';
        $this->em = $em;
        $this->repo = $em->getRepository($this->priceClass);
    }

    public function create($date, Request $request)
    {
        try {
            $date = new \DateTime($date);
        } catch (\Exception $e) {
            return $this->failResponse('Invalid date provided', 400);
        }
        try {
            $price = new Price($request->get('price'), $date, $this->createItems($request));
            if (!$this->exists($price)) {
                $this->em->persist($price);
                $this->em->flush();
            }
        } catch (ValidationException $e) {
            return $this->failResponse('Can not create price:'.$e->getMessage());
        }

        return new Response(null, 201);
    }

    private function createItems(Request $request)
    {
        $items = (array) $request->get('items');

        $productRepo = $this->em->getRepository('\Lunches\Model\Product');
        foreach ($items as $key => $item) {
            if (!array_key_exists('productId', $item) || !array_key_exists('size', $item)) {
                unset($items[$key]);
                continue;
            }
            $products = $productRepo->findBy(['id' => $item['productId']]);
            $product = array_shift($products);
            if (!$product) {
                unset($items[$key]);
                continue;
            }
            if (!in_array($item['size'], SizeWeight::$availableSizes, true)) {
                unset($items[$key]);
                continue;
            }
            $items[$key]['product'] = $product;
        }

        return $items;
    }

    private function exists(Price $price)
    {
        $prices = $this->repo->findBy(['date' => $price->getDate()]);

        foreach ($prices as $current) {
            if ($price->equalsTo($current)) {
                return true;
            }
        }
        return false;
    }
}
