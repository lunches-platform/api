<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Price;
use AppBundle\Entity\PriceFactory;
use AppBundle\ValueObject\DateRange;
use Doctrine\Bundle\DoctrineBundle\Registry;
use AppBundle\Exception\ValidationException;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PricesController
 */
class PricesController
{
    /** @var PriceFactory  */
    protected $priceFactory;

    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * PricesController constructor.
     * @param Registry $doctrine
     * @param PriceFactory $priceFactory
     */
    public function __construct(Registry $doctrine, PriceFactory $priceFactory)
    {
        $this->doctrine = $doctrine;
        $this->priceFactory = $priceFactory;
    }

    /**
     * @SWG\Get(
     *     path="/prices/{date}", tags={"price"}, operationId="getPriceAction",
     *     summary="Retrieve dish prices for a date", description="Retrieves dish prices for a date",
     *     @SWG\Parameter(
     *         description="Price date", type="string", format="date", in="path", name="date", required=true,
     *     ),
     *     @SWG\Response(
     *         response=200, description="Price",
     *         @SWG\Schema(ref="#/definitions/Price")
     *     ),
     * )
     * @param \DateTime $date
     * @return Price[]
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @View
     */
    public function getPriceAction(\DateTime $date)
    {
        $prices = $this->doctrine->getRepository('AppBundle:Price')->findByDate($date);
        if ($prices->count() === 0) {
            throw new NotFoundHttpException('There are no prices for this date');
        }

        return $prices->toArray();
    }

    /**
     * @SWG\Get(
     *     path="/prices", tags={"price"}, operationId="getPricesAction",
     *     summary="List dish prices", description="Retrieves list of dish prices by filters",
     *     @SWG\Parameter(ref="#/parameters/startDate"),
     *     @SWG\Parameter(ref="#/parameters/endDate"),
     *     @SWG\Response(response=200, description="List of Prices", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Price"))),
     * )
     * @QueryParam(name="startDate", requirements=@Assert\DateTime(format="Y-m-d"), strict=true)
     * @QueryParam(name="endDate", requirements=@Assert\DateTime(format="Y-m-d"), strict=true)
     * @param ParamFetcher $params
     * @return array
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View
     */
    public function getPricesAction(ParamFetcher $params)
    {
        try {
            $range = new DateRange($params->get('startDate'), $params->get('endDate'));
        } catch (ValidationException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        $prices = $this->doctrine->getRepository('AppBundle:Price')->findByDateRange($range);

        return $prices->toArray(true);
    }

    /**
     * @SWG\Put(
     *     path="/prices/{date}", tags={"price"}, operationId="putPriceAction",
     *     summary="Add new price", description="Adds new price for specified date",
     *     @SWG\Parameter(
     *         description="Price date", type="string", format="date", in="path", name="date", required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="body", in="body", required=true, @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Price")),
     *         description="Include here payload in Price representation",
     *     ),
     *     @SWG\Response(response=200, description="Recently added price", @SWG\Schema(ref="#/definitions/Price") ),
     * )
     * @RequestParam(name="items")
     * @RequestParam(name="price")
     * @param \DateTime $date
     * @param ParamFetcher $params
     * @return Response
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View(statusCode=201);
     */
    public function putPriceAction(\DateTime $date, ParamFetcher $params)
    {
        try {
            $price = $this->priceFactory->createFromArray([
                'date' => $date,
                'items' => $params->get('items'),
                'price' => $params->get('price'),
            ]);
        } catch (ValidationException $e) {
            throw new BadRequestHttpException('Can not create price:'.$e->getMessage());
        }
        if (!$this->exists($price)) {

            $em = $this->doctrine->getManager();
            $em->persist($price);
            $em->flush();
        }

        return new Response(null, 201);
    }

    private function exists(Price $price)
    {
        $prices = $this->doctrine->getRepository('AppBundle:Price')->findBy(['date' => $price->getDate()]);

        foreach ($prices as $current) {
            if ($price->equalsTo($current)) {
                return true;
            }
        }
        return false;
    }
}
