<?php

namespace Lunches\Controller;

use Lunches\Exception\ValidationException;
use Doctrine\ORM\EntityManager;
use Lunches\Model\OrderRepository;
use Lunches\Model\Transaction;
use Lunches\Model\TransactionRepository;
use Lunches\Model\User;
use Lunches\Model\UserRepository;
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

    public function get($transactionId, Request $request)
    {
        if (!$this->isAccessTokenValid($request)) {
            return $this->authResponse();
        }
        $transaction = $this->repo->find($transactionId);

        if (!$transaction instanceof Transaction) {
            return $this->failResponse('Transaction not found', 404);
        }

        return $this->successResponse($transaction->toArray());
    }
    public function delete($transactionId, Request $request)
    {
        if (!$this->isAccessTokenValid($request)) {
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
    public function getByUser($user, Request $request)
    {
        if (!$this->isAccessTokenValid($request)) {
            return $this->authResponse();
        }
        $user = $this->userRepo->findByUsername($user);
        $transactions = $this->repo->findByUser($user);

        return $this->successResponse(array_map(function(Transaction $transaction) {
            return $transaction->toArray();
        }, $transactions));
    }
    public function create(Request $request)
    {
        if (!$this->isAccessTokenValid($request)) {
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
            $this->payOrders($transaction);
        } catch (ValidationException $e) {
            return $this->failResponse('Transaction creation failed: '.$e->getMessage(), 400);
        }
        $this->em->persist($transaction);
        $this->em->flush();

        return new JsonResponse($transaction->toArray(), 201);
    }

    private function authResponse()
    {
        return $this->failResponse('Access token is not valid', 401);
    }

    private function isAccessTokenValid(Request $request)
    {
        $accessToken = $request->get('accessToken');
        $validToken = 'f14d16e1e90dd412d8b29ddb64168f112f753';

        return $accessToken === $validToken;
    }

    private function payOrders(Transaction $transaction)
    {
        $elapsedAmount = $transaction->getAmount();
        foreach($this->orderRepo->findNonPaidOrders($transaction->getUser()) as $order) {
            if ($elapsedAmount >= $order->getPrice()) {
                $order->pay();
                $elapsedAmount -= $order->getPrice();
            } else {
                break;
            }
        }
    }
}
