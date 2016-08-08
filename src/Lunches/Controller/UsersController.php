<?php

namespace Lunches\Controller;

use Lunches\Exception\ValidationException;
use Doctrine\ORM\EntityManager;
use Lunches\Model\User;
use Lunches\Model\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UsersController    
 */
class UsersController extends ControllerAbstract
{
    /** @var EntityManager */
    protected $em;

    /** @var UserRepository */
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
        $like = $request->get('like');
        if ($like) {
            $products = $this->repo->findByLikePattern($like);
        } else {
            $products = $this->repo->findAll();
        }

        $products = array_map(function (User $user) {
            return $user->toArray();
        }, $products);

        return $this->successResponse($products);
    }

    public function get($username)
    {
        $user = $this->repo->findByUsername($username);

        if (!$user instanceof User) {
            return $this->failResponse('User not found', 404);
        }

        return $this->successResponse($user->toArray());
    }
    public function create(Request $request)
    {
        $username = $request->get('username');
        $registeredUser = $this->repo->findByUsername($username);
        if ($registeredUser instanceof User) {
            return $this->failResponse('Such user is already registered');
        }
        $address = $request->get('address');
        try {
            $user = new User($username, $address);
        } catch (ValidationException $e) {
            return $this->failResponse('Registration failed: '.$e->getMessage(), 400);
        }
        $this->em->persist($user);
        $this->em->flush();

        return new JsonResponse($user->toArray(), 201);
    }
    public function update(Request $request)
    {
        $username = $request->get('username');
        $user = $this->repo->findByUsername($username);
        if (!$user instanceof User) {
            return $this->failResponse('User not found');
        }
        $address = $request->get('address');
        try {
            if ($address) {
                $user->changeAddress($address);
                $this->em->flush();
            }
        } catch (ValidationException $e) {
            return $this->failResponse('Update failed: '.$e->getMessage(), 400);
        }

        return new JsonResponse($user->toArray());
    }
}
