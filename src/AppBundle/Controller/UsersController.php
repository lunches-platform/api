<?php

namespace AppBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use Lunches\Exception\ValidationException;
use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * DishesController constructor.
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @SWG\Get(
     *     path="/users",
     *     description="Get list of users by filters",
     *     operationId="getUsersAction",
     *     @SWG\Parameter(
     *         description="Filter users by LIKE pattern",
     *         type="string",
     *         in="query",
     *         name="like",
     *     ),
     *     @SWG\Response(response=200, description="List of Users", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/User"))),
     * )
     * @QueryParam(name="like", description="Filter foods by LIKE pattern")
     * @param ParamFetcher $params
     * @return \Symfony\Component\HttpFoundation\Response
     * @View
     */
    public function getUsersAction(ParamFetcher $params)
    {
        $like = $params->get('like');
        $repo = $this->doctrine->getRepository('AppBundle:User');
        if ($like) {
            $products = $repo->findByLikePattern($like);
        } else {
            $products = $repo->findAll();
        }
        return $products;
    }

    /**
     * @SWG\Get(
     *     path="/users/{username}",
     *     description="Get user by username",
     *     operationId="getUserAction",
     *     @SWG\Parameter(
     *         description="Username", type="string", in="path", name="username", required=true,
     *     ),
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
     *     path="/users",
     *     operationId="postUsersAction",
     *     description="Registers new User",
     *     @SWG\Parameter(
     *         name="username",
     *         in="body",
     *         description="User name",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/User"),
     *     ),
     *     @SWG\Parameter(
     *         name="address",
     *         in="body",
     *         type="string",
     *         description="User address",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/User"),
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
     *     path="/users/{username}",
     *     operationId="putUserAction",
     *     description="Updates specified User",
     *     @SWG\Parameter(
     *         name="username",
     *         in="path",
     *         description="User name",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/User"),
     *     ),
     *     @SWG\Parameter(
     *         name="address",
     *         in="body",
     *         type="string",
     *         description="User address",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/User"),
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
