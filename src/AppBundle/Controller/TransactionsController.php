<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Transaction;
use AppBundle\Entity\User;
use AppBundle\Exception\ValidationException;
use AppBundle\ValueObject\DateRange;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;

/**
 * Class TransactionsController
 */
class TransactionsController
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * DishesController constructor.
     * @param Registry $doctrine
     * @param string $accessToken
     */
    public function __construct(Registry $doctrine, $accessToken)
    {
        $this->doctrine = $doctrine;
        $this->accessToken =$accessToken;
    }

    /**
     * @SWG\Get(
     *     tags={"transaction"},
     *     path="/transactions/{id}",
     *     description="Get Transaction by ID",
     *     operationId="getTransactionAction",
     *     @SWG\Parameter(
     *         description="id", type="string", in="path", name="id", required=true,
     *     ),
     *     @SWG\Parameter(ref="#/parameters/accessToken"),
     *     @SWG\Response(
     *         response=200, description="Transaction",
     *         @SWG\Schema(ref="#/definitions/Transaction")
     *     ),
     * )
     * @QueryParam(name="accessToken", description="Access token")
     * @param Transaction $transaction
     * @param ParamFetcher $params
     * @return Transaction
     * @View
     */
    public function getTransactionAction(Transaction $transaction, ParamFetcher $params)
    {
        $this->assertAccessGranted($params);
        return $transaction;
    }

    /**
     * @SWG\Delete(
     *     tags={"transaction"},
     *     path="/transactions/{id}",
     *     description="Delete Transaction by ID",
     *     operationId="deleteTransactionAction",
     *     @SWG\Parameter(
     *         description="id", type="string", in="path", name="id", required=true,
     *     ),
     *     @SWG\Parameter(ref="#/parameters/accessToken"),
     *     @SWG\Response(response=204, description="No response"),
     * )
     * @QueryParam(name="accessToken", description="Access token")
     * @param Transaction $transaction
     * @param ParamFetcher $params
     * @return Response
     * @throws \InvalidArgumentException
     * @View(statusCode=204)
     */
    public function deleteTransactionAction(Transaction $transaction, ParamFetcher $params)
    {
        $this->assertAccessGranted($params);
        $em = $this->doctrine->getManager();
        $em->remove($transaction);
        $em->flush();

        return new Response(null, 204);
    }

    /**
     * @SWG\Get(
     *     tags={"transaction"},
     *     path="/transactions",
     *     description="Get list of client transactions",
     *     operationId="getTransactionsAction",
     *     @SWG\Parameter(
     *         description="User clientId number",
     *         type="string",
     *         in="query",
     *         name="clientId",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         description="Review client transactions from startDate",
     *         type="string",
     *         format="date-time",
     *         in="query",
     *         name="startDate",
     *     ),
     *     @SWG\Parameter(
     *         description="Review client transactions from startDate",
     *         type="string",
     *         format="date-time",
     *         in="query",
     *         name="endDate",
     *     ),
     *     @SWG\Parameter(
     *         name="type",
     *         required=true,
     *         in="query",
     *         type="string",
     *         description="Transaction type. One of 'income', 'outcome' or 'refund' allowed",
     *         enum={"income","outcome","refund"},
     *     ),
     *     @SWG\Response(response=200, description="List of Transactions", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Transaction"))),
     * )
     * @QueryParam(name="clientId", description="User clientId number", strict=true)
     * @QueryParam(name="type", strict=true)
     * @QueryParam(name="startDate", requirements=@Assert\DateTime(format="Y-m-d"))
     * @QueryParam(name="endDate", requirements=@Assert\DateTime(format="Y-m-d"))
     * @param ParamFetcher $params
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View
     */
    public function getTransactionsAction(ParamFetcher $params)
    {
        $user = $this->doctrine->getRepository('AppBundle:User')->getByClientId($params->get('clientId'));

        $start = $params->get('startDate');
        $end = $params->get('endDate');
        $dateRange = null;
        if ($start && $end) {
            try {
                $dateRange = new DateRange($start, $end);
            } catch (ValidationException $e) {
                throw new BadRequestHttpException($e->getMessage());
            }
        }
        return $this->doctrine->getRepository('AppBundle:Transaction')->findByUser($user, $dateRange, $params->get('type'));
    }
    /**
     * @SWG\Post(
     *     tags={"transaction"},
     *     path="/transactions",
     *     operationId="postTransactionsAction",
     *     description="Registers new Transaction",
     *     @SWG\Parameter(
     *         name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/Transaction"),
     *         description="Include here payload in Transaction representation",
     *     ),
     *     @SWG\Parameter(ref="#/parameters/accessToken"),
     *     @SWG\Response(response=201, description="Newly registered transaction", @SWG\Schema(ref="#/definitions/Transaction") ),
     * )
     * @RequestParam(name="username", description="User name")
     * @RequestParam(name="type", requirements="(income|outcome|refund)")
     * @RequestParam(name="amount", requirements="\d+")
     * @RequestParam(name="paymentDate", requirements=@Assert\DateTime, strict=false)
     * @QueryParam(name="accessToken", description="Access token")
     * @param ParamFetcher $params
     * @return Transaction
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View(statusCode=201);
     */
    public function postTransactionsAction(ParamFetcher $params)
    {
        $this->assertAccessGranted($params);
        $user = $this->doctrine->getRepository('AppBundle:User')->getByUsername($params->get('username'));

        try {
            $transaction = new Transaction($params->get('type'), $params->get('amount'), $user);
            if ($paymentDate = $params->get('paymentDate')) {
                $transaction->paidAt($paymentDate);
            }
            $this->payOrders($user);
        } catch (ValidationException $e) {
            throw new BadRequestHttpException('Transaction creation failed: '.$e->getMessage());
        }
        $em = $this->doctrine->getManager();
        $em->persist($transaction);
        $em->flush();

        return $transaction;
    }

    private function payOrders(User $user)
    {
        $em = $this->doctrine->getManager();
        foreach($this->doctrine->getRepository('AppBundle:Order')->findNonPaidOrders($user) as $order) {
            $transaction = $order->pay();
            if ($transaction instanceof  Transaction) {
                $em->persist($transaction);
            }
        }
    }
    private function assertAccessGranted(ParamFetcher $params)
    {
        if ($params->get('accessToken') !== $this->accessToken) {
            throw new AccessDeniedHttpException('Access token is not valid');
        }
    }
}
