<?php

namespace Lunches\Controller;

use Lunches\Model\Image;
use Lunches\Model\ImageRepository;
use Doctrine\ORM\EntityManager;
use Lunches\Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ImagesController
 */
class ImagesController extends ControllerAbstract
{
    /** @var EntityManager */
    protected $em;

    /** @var ImageRepository */
    protected $repo;

    /** @var string  */
    protected $imageClass;

    /**
     * IngredientsController constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->imageClass = '\Lunches\Model\Image';
        $this->em = $em;
        $this->repo = $em->getRepository($this->imageClass);
    }
    /**
     * @param int $imageId
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function get($imageId)
    {
        $image = $this->repo->find($imageId);
        if (!$image) {
            return $this->failResponse('Image not found', 404);
        }
        return $this->successResponse($image->toArray());
    }

    public function create(Request $request, Application $app)
    {
        $file = $request->files->get('file');
        $result = $app['cloudinary.upload']($file);

        if (!array_key_exists('public_id', $result)) {
            return $this->failResponse('Error file uploading', 400);
        }

        $image = new Image();
        $image->setId($result['public_id']);
        $image->setUrl($result['url']);
        $image->setWidth($result['width']);
        $image->setHeight($result['height']);
        $image->setFormat($result['format']);
        $image->setCreated(new \DateTime());
        $image->setUpdated(new \DateTime());

        $this->em->persist($image);
        $this->em->flush();

        return new JsonResponse($image->toArray(), 201, [
            'Location' => $app->url('image', ['imageId' => $image->getId()])
        ]);
    }
}
