<?php

namespace Lunches\Controller;

use Lunches\Exception\ValidationException;
use Doctrine\ORM\EntityManager;
use Lunches\Model\DateRange;
use Lunches\Model\OrderRepository;
use Lunches\Model\Transaction;
use Lunches\Model\TransactionRepository;
use Lunches\Model\User;
use Lunches\Model\UserRepository;
use Lunches\Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class TransactionsController
 */
class TransactionsController extends ControllerAbstract
{
    /** @var EntityManager */
    protected $em;

    /** @var TransactionRepository */
    protected $repo;

    /** @var UserRepository */
    protected $userRepo;

    /** @var OrderRepository */
    protected $orderRepo;

    /** @var string  */
    protected $transactionClass;

    /**
     * TransactionsController constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->transactionClass = '\Lunches\Model\Transaction';
        $this->em = $em;
        $this->repo = $em->getRepository($this->transactionClass);
        $this->userRepo = $this->em->getRepository('\Lunches\Model\User');
        $this->orderRepo = $this->em->getRepository('\Lunches\Model\Order');
    }

    public function get($transactionId, Request $request, Application $app)
    {
        if (!$this->isAccessTokenValid($request, $app)) {
            return $this->authResponse();
        }
        $transaction = $this->repo->find($transactionId);

        if (!$transaction instanceof Transaction) {
            return $this->failResponse('Transaction not found', 404);
        }

        return $this->successResponse($transaction->toArray());
    }
    public function delete($transactionId, Request $request, Application $app)
    {
        if (!$this->isAccessTokenValid($request, $app)) {
            return $this->authResponse();
        }
        $transaction = $this->repo->find($transactionId);

        if (!$transaction instanceof Transaction) {
            return $this->failResponse('Transaction not found', 404);
        }
        $this->em->remove($transaction);
        $this->em->flush();

        return $this->successResponse(null, 204);
    }
    public function getList(Request $request)
    {
        $clientId = $request->get('clientId');
        if (!$clientId) {
            return $this->failResponse('Provide user clientId');
        }
        $user = $this->userRepo->findByClientId($clientId);
        if (!$user instanceof User) {
            return $this->failResponse('There is no user with specified clientId', 404);
        }

        $start = $request->get('startDate');
        $end = $request->get('endDate');
        $dateRange = null;
        if ($start && $end) {
            try {
                $dateRange = new DateRange($start, $end);
            } catch (ValidationException $e) {
                return $this->failResponse($e->getMessage());
            }
        }
        $transactions = $this->repo->findByUser($user, $dateRange, $request->get('type'));

        return $this->successResponse(array_map(function(Transaction $transaction) {
            return $transaction->toArray();
        }, $transactions));
    }
    public function create(Request $request, Application $app)
    {
        if (!$this->isAccessTokenValid($request, $app)) {
            return $this->authResponse();
        }
        $username = $request->get('username');
        $type     = $request->get('type');
        $amount   = $request->get('amount');

        $user = $this->userRepo->findByUsername($username);
        if (!$user instanceof User) {
            return $this->failResponse('There is no such user', 404);
        }

        try {
            $transaction = new Transaction($type, $amount, $user);
            if ($paymentDate = $request->get('paymentDate')) {
                $transaction->paidAt($paymentDate);
            }
            $this->payOrders($user);
        } catch (ValidationException $e) {
            return $this->failResponse('Transaction creation failed: '.$e->getMessage(), 400);
        }
        $this->em->persist($transaction);
        $this->em->flush();

        return new JsonResponse($transaction->toArray(), 201);
    }

    private function payOrders(User $user)
    {
        foreach($this->orderRepo->findNonPaidOrders($user) as $order) {
            $transaction = $order->pay();
            if ($transaction instanceof  Transaction) {
                $this->em->persist($transaction);
            }
        }
    }
}
