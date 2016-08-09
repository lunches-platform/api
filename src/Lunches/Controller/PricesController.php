<?php

namespace Lunches\Controller;

use Doctrine\ORM\EntityManager;
use Lunches\Exception\ValidationException;
use Lunches\Model\DateRange;
use Lunches\Model\Price;
use Lunches\Model\PriceFactory;
use Lunches\Model\PriceRepository;
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

    /** @var PriceFactory  */
    protected $priceFactory;

    /**
     * ProductsController constructor.
     *
     * @param EntityManager $em
     * @param PriceFactory $priceFactory
     */
    public function __construct(EntityManager $em, PriceFactory $priceFactory)
    {
        $this->priceClass = '\Lunches\Model\Price';
        $this->em = $em;
        $this->repo = $em->getRepository($this->priceClass);
        $this->priceFactory = $priceFactory;
    }

    public function get($date)
    {
        try {
            $date = new \DateTime($date);
        } catch (\Exception $e) {
            return $this->failResponse('Invalid date provided', 400);
        }
        $prices = $this->repo->findByDate($date);
        if ($prices->count() === 0) {
            return $this->failResponse('There are no prices for this date', 404);
        }

        return $this->successResponse($prices->toArray());
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @throws ValidationException
     */
    public function getList(Request $request)
    {
        try {
            $range = new DateRange($request->get('startDate'), $request->get('endDate'));
        } catch (ValidationException $e) {
            return $this->failResponse($e->getMessage(), 400);
        }

        $prices = $this->repo->findByDateRange($range);

        return $this->successResponse($prices->toArray());
    }

    public function create($date, Request $request)
    {
        try {
            $price = $this->priceFactory->createFromArray(array_merge($request->request->all(), ['date' => $date]));
        } catch (ValidationException $e) {
            return $this->failResponse('Can not create price:'.$e->getMessage(), 400);
        }
        if (!$this->exists($price)) {
            $this->em->persist($price);
            $this->em->flush();
        }

        return new Response(null, 201);
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
