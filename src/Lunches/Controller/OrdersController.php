<?php

namespace Lunches\Controller;

use Lunches\Exception\RuntimeException;
use Lunches\Exception\ValidationException;
use Lunches\Model\DateRange;
use Lunches\Model\Order;
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

    public function getByUser($user, Request $request)
    {
        $range = null;
        $start = $request->get('startDate') ?: new \DateTime('monday last week');
        $end = $request->get('endDate') ?: new \DateTime('friday next week');

        if ($start && $end) {
            try {
                $range = new DateRange($start, $end);
            } catch (ValidationException $e) {
                return $this->failResponse($e->getMessage(), 400);
            }
        }
        $orders = $this->repo->findByUsername($user, $range);
        if (!count($orders)) {
            return $this->failResponse('Orders not found', 404);
        }

        return $this->successResponse(array_map(function(Order $order) {
            return $order->toArray();
        }, $orders));
    }

    /**
     * @param Request $request
     * @param Application $app
     * @return Response
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\ORMInvalidArgumentException
     * @throws \Lunches\Exception\RuntimeException
     * @throws \InvalidArgumentException
     */
    public function create(Request $request, Application $app)
    {
        $data = (array) $request->request->all();

        try {
            $order = $this->orderFactory->createNewFromArray($data);

            if ($this->validator->isValid($order)) {
                $this->em->persist($order);
                $this->em->flush();

                return $this->successResponse($order->toArray(), 201, [
                    'Location' => $app->url('order', ['orderId' => $order->getId()])
                ]);
            } else {
                $errors = $this->validator->getErrors();
            }
        } catch (ValidationException $e) {
            $errors['order'] = $e->getMessage();
        } catch (RuntimeException $e) {
            $errors['order'] = $e->getMessage();
        }

        return $this->failResponse('Invalid input data provided', 400, $errors);
    }
}
