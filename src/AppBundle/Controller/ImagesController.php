<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations\FileParam;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcher;
use Speicher210\CloudinaryBundle\Cloudinary\Uploader;
use Swagger\Annotations AS SWG;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class ImagesController
 */
class ImagesController
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * ImagesController constructor.
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine)
    {
        $this->doctrine = $doctrine;
    }
    /**
     * @SWG\Get(
     *     path="/images/{imageId}",
     *     description="Get Image by ID",
     *     operationId="getImageAction",
     *     @SWG\Parameter(
     *         description="ID of image", type="string", in="path", name="imageId", required=true,
     *     ),
     *     @SWG\Response(
     *         response=200, description="Image",
     *         @SWG\Schema(ref="#/definitions/Image")
     *     ),
     * )
     * @param Image $image
     * @return Image
     * @View
     */
    public function getImageAction(Image $image)
    {
        return $image;
    }

    /**
     * @SWG\Post(
     *     path="/images",
     *     operationId="postImages",
     *     description="Uploads new image",
     *     @SWG\Parameter(
     *         name="file",
     *         type="file",
     *         in="formData",
     *         description="File to upload",
     *         required=true
     *     ),
     *     @SWG\Response(response=201, description="Uploaded Image", @SWG\Schema(ref="#/definitions/Image") ),
     * )
     *
     * @FileParam(image=true, name="file", strict=true)
     *
     * @param ParamFetcher $params
     * @return Image
     * @throws \InvalidArgumentException
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @View(statusCode=201);
     */
    public function postImagesAction(ParamFetcher $params)
    {
        $result = Uploader::upload($params->get('file'));

        if (!array_key_exists('public_id', $result)) {
            throw new HttpException(400, 'Error file uploading');
        }

        $image = new Image();
        $image->setId($result['public_id']);
        $image->setUrl($result['url']);
        $image->setWidth($result['width']);
        $image->setHeight($result['height']);
        $image->setFormat($result['format']);
        $image->setCreated(new \DateTime());
        $image->setUpdated(new \DateTime());

        $em = $this->doctrine->getManager();
        $em->persist($image);
        $em->flush();

        return $image;
    }
}
