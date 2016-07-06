<?php

namespace Lunches\Controller;

use Lunches\Model\OrderFactory;
use Lunches\Model\OrderRepository;
use Lunches\Validator\OrderValidator;
use Doctrine\ORM\EntityManager;
use Lunches\Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class OrdersController
 */
class OrdersController extends ControllerAbstract
{
    /** @var EntityManager */
    protected $em;

    /** @var OrderRepository */
    protected $repo;

    /** @var OrderValidator */
    protected $validator;
    
    /** @var OrderFactory  */
    protected $orderFactory;

    /** @var string  */
    protected $orderClass;

    /**
     * OrdersController constructor.
     *
     * @param EntityManager $em
     * @param OrderFactory $orderFactory
     * @param OrderValidator $validator
     */
    public function __construct(EntityManager $em, OrderFactory $orderFactory, OrderValidator $validator)
    {
        $this->orderClass = '\Lunches\Model\Order';
        $this->validator = $validator;
        $this->orderFactory = $orderFactory;
        $this->em = $em;
        $this->repo = $em->getRepository($this->orderClass);
    }

    /**
     * @param int $orderId
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function get($orderId)
    {
        $order = $this->repo->find($orderId);
        if (!$order) {
            return $this->failResponse('Order not found', 404);
        }
        return $this->successResponse($order->toArray());
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return JsonResponse
     */
    public function create(Request $request, Application $app)
    {
        $data = (array) $request->request->all();

        $order = $this->orderFactory->createNewFromArray($data);

        if ($this->validator->isValid($order)) {
            $this->em->persist($order);
            $this->em->flush();

            return new Response(null, 201, [
                'Location' => $app->url('order', ['orderId' => $order->getId()])
            ]);
        }
        return $this->failResponse('Invalid input data provided', 400, $this->validator->getErrors());
    }
}
