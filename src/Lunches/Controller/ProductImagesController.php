<?php

namespace Lunches\Controller;

use Lunches\Model\Image;
use Lunches\Model\ImageRepository;
use Lunches\Model\Product;
use Lunches\Model\ProductImage;
use Doctrine\ORM\EntityManager;
use Lunches\Model\ProductImageRepository;
use Lunches\Model\ProductRepository;
use Lunches\Silex\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ProductImagesController
 */
class ProductImagesController extends ControllerAbstract
{
    /** @var EntityManager */
    protected $em;

    /** @var ProductImageRepository */
    protected $repo;

    /** @var ProductRepository */
    protected $productRepo;

    /** @var ImageRepository */
    protected $imageRepo;

    /** @var string  */
    protected $productImageClass;

    /**
     * IngredientsController constructor.
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
    {
        $this->productImageClass = '\Lunches\Model\ProductImage';
        $this->em = $em;
        $this->repo = $em->getRepository($this->productImageClass);
        $this->productRepo = $em->getRepository('\Lunches\Model\Product');
        $this->imageRepo = $em->getRepository('\Lunches\Model\Image');
    }
    /**
     * @param string $imageId
     * @param int    $productId
     * @return JsonResponse
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function get($imageId, $productId)
    {
        /** @var ProductImage $image */
        $image = $this->repo->findOneBy([
            'image' => $imageId,
            'product' => $productId,
        ]);
        if (!$image) {
            return $this->failResponse('Image not found', 404);
        }
        return $this->successResponse($image->toArray());
    }

    /**
     * @param int $productId
     * @return JsonResponse
     */
    public function getList($productId)
    {
        /** @var ProductImage[] $images */
        $images = $this->repo->findBy([
            'product' => $productId
        ]);

        if (0 === count($images)) {
            return $this->failResponse('Images not found', 404);
        }
        return $this->successResponse(array_map(function (ProductImage $image) {
            return $image->toArray(true);
        }, $images));
    }

    public function create($productId, $imageId, Application $app, Request $request)
    {
        /** @var Product $product */
        $product = $this->productRepo->find($productId);
        if (!$product instanceof Product) {
            return $this->failResponse('There is no such Product', 404);
        }
        /** @var Image $image */
        $image = $this->imageRepo->find($imageId);
        if (!$image instanceof Image) {
            return $this->failResponse('There is no such Image', 404);
        }
        $productImage = new ProductImage();
        $productImage->setProduct($product);
        $productImage->setImage($image);
        $productImage->setIsCover($request->get('isCover', false));
        $productImage->setCreated(new \DateTime('now'));
        $productImage->setUpdated(new \DateTime('now'));

        if ($product->hasImage($productImage)) {
            return $this->failResponse('Duplicate image', 400);
        }
        $product->addImage($productImage);

        $this->em->flush();

        return new JsonResponse($productImage->toArray(), 201, [
            'Location' => $app->url('product-image', [
                'imageId' => $imageId,
                'productId' => $productId
            ])
        ]);
    }

    public function markCover($productId, $imageId)
    {
        /** @var ProductImage $image */
        $image = $this->repo->findOneBy([
            'image' => $imageId,
            'product' => $productId,
        ]);
        if (!$image) {
            return $this->failResponse('Such image is not assigned to this product', 404);
        }
        $image->setIsCover(true);
        $this->em->flush();
        
        return $this->successResponse(null, 204);
    }
}
