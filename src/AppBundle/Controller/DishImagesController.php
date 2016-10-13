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
     *     path="/dishes/{dishId}/images/{imageId}", tags={"Dish Images"}, operationId="getDishImageAction",
     *     summary="Retrieve dish image", description="Retrieves DishImage by dish ID and image ID",
     *     @SWG\Parameter(ref="#/parameters/dishId"),
     *     @SWG\Parameter(ref="#/parameters/imageId"),
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
     *     path="/dishes/{dishId}/images", tags={"Dish Images"}, operationId="getDishImages",
     *     summary="List all dish images", description="Returns all assigned dish images",
     *     @SWG\Parameter(ref="#/parameters/dishId"),
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
     *     path="/dishes/{dishId}/images/{imageId}", tags={"Dish Images"}, operationId="putDishImageAction",
     *     summary="Assign an image to dish", description="Assigns new image for the dish. Operation is idempotent",
     *     @SWG\Parameter(ref="#/parameters/dishId"),
     *     @SWG\Parameter(ref="#/parameters/imageId"),
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Include here payload in DishImage representation excluding **dish** and **image** properties (as it has been already defined in _path_ parameters)",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/DishImage"),
     *     ),
     *     @SWG\Response(response=201, description="Added DishImage", @SWG\Schema(ref="#/definitions/DishImage") ),
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
     *     path="/dishes/{dishId}/images/{imageId}/cover", tags={"Dish Images"}, operationId="putDishCoverImage",
     *     summary="Updates cover image for the dish", description="Assigns cover image for the dish. Resets any previously assigned cover",
     *     @SWG\Parameter(ref="#/parameters/dishId"),
     *     @SWG\Parameter(ref="#/parameters/imageId"),
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
