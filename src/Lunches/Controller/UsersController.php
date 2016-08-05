<?php

namespace Lunches\Controller;

use Lunches\Exception\ValidationException;
use Lunches\Model\MenuRepository;
use Doctrine\ORM\EntityManager;
use Lunches\Model\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UsersController    
 */
class UsersController extends ControllerAbstract
{
    /** @var EntityManager */
    protected $em;

    /** @var MenuRepository */
    protected $repo;

    /** @var string  */
    protected $userClass;

    /**
     * MenusController constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->userClass = '\Lunches\Model\User';
        $this->em = $em;
        $this->repo = $em->getRepository($this->userClass);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @throws ValidationException
     */
    public function getList(Request $request)
    {
        $products = $this->repo->findAll();

        if (0 === count($products)) {
            return $this->failResponse('Users not found', 404);
        }

        $products = array_map(function (User $user) {
            return $user->toArray();
        }, $products);

        return $this->successResponse($products);
    }
}
