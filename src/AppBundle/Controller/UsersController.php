<?php

namespace AppBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use AppBundle\Exception\ValidationException;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Swagger\Annotations as SWG;

/**
 * Class UsersController    
 */
class UsersController
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * UsersController constructor.
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @SWG\Get(
     *     path="/users", tags={"Users"}, operationId="getUsersAction",
     *     summary="List all users", description="Get list of users by filters",
     *     @SWG\Parameter(ref="#/parameters/like"),
     *     @SWG\Response(response=200, description="List of Users", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/User"))),
     * )
     * @QueryParam(name="like")
     * @param ParamFetcher $params
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View
     */
    public function getUsersAction(ParamFetcher $params, Request $request)
    {
        $repo = $this->doctrine->getRepository('AppBundle:User');
        if ($request->query->has('like')) {
            $users = $repo->findByLikePattern($params->get('like'));
        } else {
            $users = $repo->findAll();
        }
        return $users;
    }

    /**
     * @SWG\Get(
     *     path="/users/{username}", tags={"Users"}, operationId="getUserAction",
     *     summary="Retrieve user", description="Retrieves user by username",
     *     @SWG\Parameter(ref="#/parameters/username"),
     *     @SWG\Response(
     *         response=200, description="User",
     *         @SWG\Schema(ref="#/definitions/User")
     *     ),
     * )
     * @ParamConverter("user", options={"id":"user", "repository_method":"findByUsername"})
     * @param User $user
     * @return User
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @View
     */
    public function getUserAction(User $user)
    {
        return $user;
    }

    /**
     * @SWG\Post(
     *     path="/users", tags={"Users"}, operationId="postUsersAction",
     *     summary="Register user", description="Registers new User",
     *     @SWG\Parameter(
     *         name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/User"),
     *         description="Provide here payload in User representation",
     *     ),
     *     @SWG\Response(response=201, description="Newly registered User", @SWG\Schema(ref="#/definitions/User") ),
     * )
     * @RequestParam(name="username", description="User name")
     * @RequestParam(name="address", description="User address")
     * @param ParamFetcher $params
     * @return User
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View(statusCode=201);
     */
    public function postUsersAction(ParamFetcher $params)
    {
        $repo = $this->doctrine->getRepository('AppBundle:User');

        $registeredUser = $repo->findByUsername($username = $params->get('username'));
        if ($registeredUser instanceof User) {
            throw new BadRequestHttpException('Such user is already registered');
        }
        try {
            $user = new User($repo->generateClientId(), $username, $params->get('address'));
        } catch (ValidationException $e) {
            throw new BadRequestHttpException('Registration failed: '.$e->getMessage());
        }
        $em = $this->doctrine->getManager();
        $em->persist($user);
        $em->flush();

        return $user;
    }

    /**
     * @SWG\Put(
     *     path="/users/{username}", tags={"Users"}, operationId="putUserAction",
     *     summary="Update user details", description="Updates specified User. It is allowed to update user address currently",
     *     @SWG\Parameter(ref="#/parameters/username"),
     *     @SWG\Parameter(
     *         name="body", in="body", required=true, @SWG\Schema(ref="#/definitions/User"),
     *         description="",
     *     ),
     *     @SWG\Response(response=200, description="User with updated fields", @SWG\Schema(ref="#/definitions/User") ),
     * )
     * @RequestParam(name="address", description="User address")
     * @ParamConverter("user", options={"id":"user", "repository_method":"findByUsername"})
     * @param User $user
     * @param ParamFetcher $params
     * @return User
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @throws \InvalidArgumentException
     * @View
     */
    public function putUserAction(User $user, ParamFetcher $params)
    {
        try {
            if ($address = $params->get('address')) {
                $user->changeAddress($address);
                $this->doctrine->getManager()->flush();
            }
        } catch (ValidationException $e) {
            throw new BadRequestHttpException('Update failed: '.$e->getMessage());
        }

        return $user;
    }
}
