<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Order;
use AppBundle\Entity\Transaction;
use AppBundle\Entity\User;
use AppBundle\OrderFactory;
use AppBundle\ValueObject\DateRange;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations\Post;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * Class OrdersController
 */
class OrdersController
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /** @var OrderFactory  */
    protected $orderFactory;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * OrdersController constructor.
     * @param Registry $doctrine
     * @param OrderFactory $orderFactory
     * @param string $accessToken
     */
    public function __construct(Registry $doctrine, OrderFactory $orderFactory, $accessToken)
    {
        $this->doctrine = $doctrine;
        $this->orderFactory = $orderFactory;
        $this->accessToken = $accessToken;
    }

    /**
     * @SWG\Get(
     *     tags={"order"},
     *     path="/orders",
     *     description="Get list of orders by filters",
     *     operationId="getOrdersAction",
     *     @SWG\Parameter(
     *         name="shipmentDate",
     *         description="Filter orders which will be shipped on specified date",
     *         type="string",
     *         format="date-time",
     *         in="query",
     *     ),
     *     @SWG\Parameter(
     *         description="Get orders which shipmentDate greater than startDate",
     *         type="string",
     *         format="date-time",
     *         in="query",
     *         name="startDate",
     *     ),
     *     @SWG\Parameter(
     *         description="Get orders which shipmentDate less than endDate",
     *         type="string",
     *         format="date-time",
     *         in="query",
     *         name="endDate",
     *     ),
     *     @SWG\Response(response=200, description="List of Orders", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Order"))),
     * )
     * @QueryParam(name="shipmentDate", requirements=@Assert\DateTime(format="Y-m-d"), strict=false)
     * @QueryParam(name="startDate", requirements=@Assert\DateTime(format="Y-m-d"), strict=false)
     * @QueryParam(name="endDate", requirements=@Assert\DateTime(format="Y-m-d"), strict=false)
     * @param ParamFetcher $params
     * @return Order[]
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View
     */
    public function getOrdersAction(ParamFetcher $params)
    {
        try {
            $shipmentDate = $params->get('shipmentDate') ? new \DateTime($params->get('shipmentDate')) : null;
            $dateRange = $this->createDateRange($params, false, false);
            $filters = array_filter([
                'shipmentDate' => $shipmentDate,
                'dateRange' => $dateRange,
            ]);
        } catch (\Exception $e) {
            throw new BadRequestHttpException('Invalid filters: ' . $e->getMessage());
        }

        if (count($filters) === 0) {
            throw new BadRequestHttpException('Provide one or more filters to obtain orders');
        }

        return $this->doctrine->getRepository('AppBundle:Order')->getList($filters);
    }

    /**
     * @SWG\Get(
     *     tags={"order"},
     *     path="/orders/{id}",
     *     description="Get order by ID",
     *     operationId="getOrderAction",
     *     @SWG\Parameter(
     *         description="ID of Order", type="integer", in="path", name="id", required=true,
     *     ),
     *     @SWG\Response(
     *         response=200, description="Order",
     *         @SWG\Schema(ref="#/definitions/Order")
     *     ),
     * )
     * @param Order $order
     * @View
     * @return Order
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function getOrderAction(Order $order)
    {
        return $order;
    }

    /**
     * @SWG\Get(
     *     tags={"order"},
     *     path="/users/{user}/orders",
     *     description="Get list of user orders using filters if needed",
     *     operationId="getUserOrdersAction",
     *     @SWG\Parameter(
     *         description="Username", type="string", in="path", name="username", required=true,
     *     ),
     *     @SWG\Parameter(
     *         description="Get user orders which shipmentDate greater than startDate",
     *         type="string",
     *         format="date-time",
     *         in="query",
     *         name="startDate",
     *     ),
     *     @SWG\Parameter(
     *         description="Get user orders which shipmentDate less than endDate",
     *         type="string",
     *         format="date-time",
     *         in="query",
     *         name="endDate",
     *     ),
     *     @SWG\Parameter(
     *         name="paid",
     *         default=false,
     *         in="query",
     *         type="boolean",
     *         description="Whether filter out non paid orders or no",
     *         enum={"0","1",false,true},
     *     ),
     *     @SWG\Parameter(
     *         name="withCanceled",
     *         default=false,
     *         in="query",
     *         type="boolean",
     *         description="Whether include canceled orders or no",
     *         enum={"0","1",false,true},
     *     ),
     *     @SWG\Response(response=200, description="List of Orders", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Order"))),
     * )
     * @QueryParam(name="startDate", requirements=@Assert\DateTime(format="Y-m-d"), strict=true)
     * @QueryParam(name="endDate", requirements=@Assert\DateTime(format="Y-m-d"), strict=true)
     * @QueryParam(name="paid", requirements="(0|1|true|false)", default=false)
     * @QueryParam(name="withCanceled", requirements="(0|1|true|false)", default=false)
     * @ParamConverter("user", options={"id":"user", "repository_method":"findByUsername"})
     * @param User $user
     * @param ParamFetcher $params
     * @return Order[]
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @View
     */
    public function getUserOrdersAction(User $user, ParamFetcher $params)
    {
        $range = $this->createDateRange($params);
        $orders = $this->doctrine->getRepository('AppBundle:Order')->findByUser($user, $params->get('paid', null), $params->get('withCanceled', 0), $range);
        if (!count($orders)) {
            throw new NotFoundHttpException('Orders not found');
        }

        return $orders;
    }

    /**
     * @SWG\Post(
     *     tags={"order"},
     *     path="/orders",
     *     operationId="postOrdersAction",
     *     description="Place an order",
     *     @SWG\Parameter(
     *         name="body", in="body", required=true,  @SWG\Schema(ref="#/definitions/Order"),
     *         description="Include here payload in Order representation",
     *     ),
     *     @SWG\Response(response=201, description="Newly placed Order", @SWG\Schema(ref="#/definitions/Order") ),
     * )
     * @RequestParam(name="userId", requirements=@Assert\Uuid())
     * @RequestParam(name="address", strict=false)
     * @RequestParam(name="shipmentDate", requirements=@Assert\DateTime(format="Y-m-d"))
     * @RequestParam(name="items")
     * @param ParamFetcher $params
     * @return Order
     * @throws \AppBundle\Exception\OrderException
     * @throws \AppBundle\Exception\ValidationException
     * @throws \AppBundle\Exception\RuntimeException
     * @throws \AppBundle\Exception\LineItemException
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View(statusCode=201);
     */
    public function postOrdersAction(ParamFetcher $params)
    {
        $em = $this->doctrine->getManager();
        $order = $this->orderFactory->createNewFromArray((array) $params->all());
        $transaction = $order->pay();
        if ($transaction instanceof Transaction) {
            $em->persist($transaction);
        }
        $em->persist($order);
        $em->flush();

        return $order;
    }

    /**
     * @SWG\Post(
     *     tags={"order"},
     *     path="/orders/{id}/cancel",
     *     operationId="postOrdersCancelAction",
     *     description="Cancel an order",
     *     @SWG\Parameter(
     *         description="ID of Order", type="integer", in="path", name="id", required=true,
     *     ),
     *     @SWG\Response(response=201, description="Canceled Order", @SWG\Schema(ref="#/definitions/Order") ),
     * )
     * @Post("/orders/{id}/cancel")
     * @RequestParam(name="reason")
     * @param Order $order
     * @param ParamFetcher $params
     * @return Order
     * @throws \AppBundle\Exception\ValidationException
     * @throws \InvalidArgumentException
     * @throws \AppBundle\Exception\OrderException
     * @View(statusCode=201);
     */
    public function postOrdersCancelAction(Order $order, ParamFetcher $params)
    {
        $em = $this->doctrine->getManager();
        $transaction = $order->cancel($params->get('reason'));
        if ($transaction instanceof Transaction) {
            $em->persist($transaction);
        }
        $em->flush();

        return $order;
    }

    /**
     * @SWG\Post(
     *     tags={"order"},
     *     path="/orders/{id}/reject",
     *     operationId="postOrdersRejectAction",
     *     description="Reject an order",
     *     @SWG\Parameter(
     *         description="ID of Order", type="integer", in="path", name="id", required=true,
     *     ),
     *     @SWG\Parameter(ref="#/parameters/accessToken"),
     *     @SWG\Response(response=201, description="Rejected Order", @SWG\Schema(ref="#/definitions/Order") ),
     * )
     * @Post("/orders/{id}/reject")
     * @QueryParam(name="accessToken", description="Access token")
     * @RequestParam(name="reason")
     * @param Order $order
     * @param ParamFetcher $params
     * @return Order
     * @throws \InvalidArgumentException
     * @View(statusCode=201);
     */
    public function postOrdersRejectAction(Order $order, ParamFetcher $params)
    {
        $this->assertAccessGranted($params);
        $transaction = $order->reject($params->get('reason'));

        $em = $this->doctrine->getManager();
        if ($transaction instanceof Transaction) {
            $em->persist($transaction);
        }
        $em->flush();

        return $order;
    }
    /**
     * @SWG\Put(
     *     tags={"order"}, path="/orders/{id}", operationId="putOrderAction",
     *     description="Updates specified Order",
     *     @SWG\Parameter(
     *         name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/Order"),
     *     ),
     *     @SWG\Response(response=200, description="Updated order", @SWG\Schema(ref="#/definitions/Order") ),
     * )
     * @RequestParam(name="address")
     * @param Order $order
     * @param ParamFetcher $params
     * @return Order
     * @throws \InvalidArgumentException
     * @View
     */
    public function putOrdersAction(Order $order, ParamFetcher $params)
    {
        $address = $params->get('address');
        if ($address) {
            $order->changeAddress($address);
            $this->doctrine->getManager()->flush();
        }
        return $order;
    }

    private function createDateRange(ParamFetcher $params, $required = false, $default = true)
    {
        $start = $params->get('startDate');
        if (!$start && $default === true) {
            $start = new \DateTime('monday last week');
        }
        $end = $params->get('endDate');
        if (!$end && $default === true) {
            $end = new \DateTime('friday next week');
        }
        if (!$required && !($start || $end)) {
            return null;
        }

        return new DateRange($start, $end);
    }
    private function assertAccessGranted(ParamFetcher $params)
    {
        if ($params->get('accessToken') !== $this->accessToken) {
            throw new AccessDeniedHttpException('Access token is not valid');
        }
    }
}
