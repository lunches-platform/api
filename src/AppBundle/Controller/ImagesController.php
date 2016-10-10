<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Image;
use Doctrine\Bundle\DoctrineBundle\Registry;
use FOS\RestBundle\Controller\Annotations\View;
use Swagger\Annotations AS SWG;

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
     * @return \Symfony\Component\HttpFoundation\Response
     * @View
     */
    public function getImageAction(Image $image)
    {
        return $image;
    }

//    public function create(Request $request, Application $app)
//    {
//        $file = $request->files->get('file');
//        if (!$file instanceof UploadedFile) {
//            return $this->failResponse('File with "file" query param name is not found');
//        }
//        $result = $app['cloudinary.upload']($file);
//
//        if (!array_key_exists('public_id', $result)) {
//            return $this->failResponse('Error file uploading', 400);
//        }
//
//        $image = new Image();
//        $image->setId($result['public_id']);
//        $image->setUrl($result['url']);
//        $image->setWidth($result['width']);
//        $image->setHeight($result['height']);
//        $image->setFormat($result['format']);
//        $image->setCreated(new \DateTime());
//        $image->setUpdated(new \DateTime());
//
//        $this->em->persist($image);
//        $this->em->flush();
//
//        return new JsonResponse($image->toArray(), 201, [
//            'Location' => $app->url('image', ['imageId' => $image->getId()])
//        ]);
//    }
}
