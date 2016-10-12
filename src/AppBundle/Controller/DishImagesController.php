<?php

namespace AppBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Registry;
use AppBundle\Entity\Image;
use AppBundle\Entity\Dish;
use AppBundle\Entity\DishImage;
use FOS\RestBundle\Controller\Annotations\Put;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\Type;
use Swagger\Annotations AS SWG;

/**
 * Class DishImagesController
 */
class DishImagesController
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * DishImagesController constructor.
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }

    /**
     * @SWG\Get(
     *     path="/dishes/{dishId}/images/{imageId}",
     *     description="Get dish image ID and dish ID",
     *     operationId="getDishImageAction",
     *     @SWG\Parameter(
     *         description="ID of dish", type="string", in="path", name="dishId", required=true,
     *     ),
     *     @SWG\Parameter(
     *         description="ID of image", type="string", in="path", name="imageId", required=true,
     *     ),
     *     @SWG\Response(
     *         response=200, description="DishImage object",
     *         @SWG\Schema(ref="#/definitions/DishImage")
     *     ),
     * )
     * @param string $dishId
     * @param string $imageId
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @View
     */
    public function getImageAction($dishId, $imageId)
    {
        return $this->doctrine->getRepository('AppBundle:DishImage')->findOneBy([
            'image' => $imageId,
            'dish' => $dishId,
        ]);
    }

    /**
     * @SWG\Get(
     *     path="/dishes/{dishId}/images",
     *     description="Fetches all dish images",
     *     operationId="getDishImages",
     *     @SWG\Response(response=200, description="List of DishImages", @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/DishImage"))),
     * )
     * @param int $dishId
     * @return \Symfony\Component\HttpFoundation\Response
     * @View
     */
    public function getImagesAction($dishId)
    {
        return $this->doctrine->getRepository('AppBundle:DishImage')->findBy([
            'dish' => $dishId,
        ]);
    }

    /**
     * @SWG\Put(
     *     path="/dishes/{dishId}/images/{imageId}",
     *     operationId="putDishImageAction",
     *     description="Adds image to dish",
     *     @SWG\Parameter(
     *         description="ID of dish", type="string", in="path", name="dishId", required=true,
     *     ),
     *     @SWG\Parameter(
     *         description="ID of image", type="string", in="path", name="imageId", required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="name",
     *         in="body",
     *         description="Category name",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Category"),
     *     ),
     *     @SWG\Parameter(name="description", in="body", @SWG\Schema(ref="#/definitions/Category")),
     *     @SWG\Response(response=201, description="Newly created Category", @SWG\Schema(ref="#/definitions/Category") ),
     * )
     *
     * @RequestParam(name="isCover", requirements=@Type("bool"), default=false)
     *
     * @param Dish $dish
     * @param Image $image
     * @param ParamFetcher $params
     * @return DishImage
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @View(statusCode=201);
     */
    public function putImageAction(Dish $dish, Image $image, ParamFetcher $params)
    {
        $dishImage = new DishImage();
        $dishImage->setDish($dish);
        $dishImage->setImage($image);
        $dishImage->setIsCover($params->get('isCover'));
        $dishImage->setCreated(new \DateTime('now'));
        $dishImage->setUpdated(new \DateTime('now'));

        if ($dish->hasImage($dishImage)) {
            throw new BadRequestHttpException('Duplicate image');
        }
        $dish->addImage($dishImage);

        $this->doctrine->getManager()->flush();

        return $dishImage;
    }

    /**
     * @SWG\Put(
     *     path="/dishes/{dishId}/images/{imageId}/cover",
     *     operationId="putDishCoverImage",
     *     description="Assigns cover image for the dish. Resets any previously assigned cover",
     *     @SWG\Parameter(
     *         description="ID of dish", type="string", in="path", name="dishId", required=true,
     *     ),
     *     @SWG\Parameter(
     *         description="ID of image", type="string", in="path", name="imageId", required=true,
     *     ),
     *     @SWG\Response(response=204, description="No response"),
     * )
     * @Put("/dishes/{dishId}/images/{imageId}/cover")
     * @param string $dishId
     * @param string $imageId
     * @return Response
     * @throws \InvalidArgumentException
     */
    public function putImagesCoverAction($dishId, $imageId)
    {
        $dishImage = $this->doctrine->getRepository('AppBundle:DishImage')->findOneBy([
            'image' => $imageId,
            'dish' => $dishId,
        ]);
        $dishImage->setIsCover(true);
        $this->doctrine->getManager()->flush();

        return new Response();
    }
}
