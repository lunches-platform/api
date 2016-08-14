<?php

namespace Lunches\Controller;

use Lunches\Exception\ValidationException;
use Doctrine\ORM\EntityManager;
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
    }

    public function get($transactionId)
    {
        $transaction = $this->repo->find($transactionId);

        if (!$transaction instanceof Transaction) {
            return $this->failResponse('Transaction not found', 404);
        }

        return $this->successResponse($transaction->toArray());
    }
    public function delete($transactionId)
    {
        $transaction = $this->repo->find($transactionId);

        if (!$transaction instanceof Transaction) {
            return $this->failResponse('Transaction not found', 404);
        }
        $this->em->remove($transaction);
        $this->em->flush();

        return $this->successResponse(null, 204);
    }
    public function getByUser($user)
    {
        $user = $this->userRepo->findByUsername($user);
        $transactions = $this->repo->findByUser($user);

        return $this->successResponse(array_map(function(Transaction $transaction) {
            return $transaction->toArray();
        }, $transactions));
    }
    public function create(Request $request)
    {
        $username = $request->get('username');
        $type     = $request->get('type');
        $amount   = $request->get('amount');

        $user = $this->userRepo->findByUsername($username);
        if (!$user instanceof User) {
            return $this->failResponse('There is no such user', 404);
        }

        try {
            $transaction = new Transaction($type, $amount, $user);
        } catch (ValidationException $e) {
            return $this->failResponse('Transaction creation failed: '.$e->getMessage(), 400);
        }
        $this->em->persist($transaction);
        $this->em->flush();

        return new JsonResponse($transaction->toArray(), 201);
    }
}
