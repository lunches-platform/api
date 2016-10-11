<?php

namespace AppBundle\Entity;

use AppBundle\Exception\RuntimeException;
use Doctrine\ORM\EntityRepository;

/**
 * DishImageRepository
 */
class DishImageRepository extends EntityRepository
{
    public function get($dishId, $imageId)
    {
        $dishImage = $this->findOneBy([
            'image' => $imageId,
            'dish' => $dishId,
        ]);
        if (!$dishImage instanceof DishImage) {
            throw RuntimeException::notFound('DishImage');
        }
        return $dishImage;
    }
}
