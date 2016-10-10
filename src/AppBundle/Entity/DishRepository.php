<?php

namespace AppBundle\Entity;

use AppBundle\Exception\RuntimeException;
use Doctrine\ORM\EntityRepository;

/**
 * DishRepository
 */
class DishRepository extends EntityRepository
{
    /**
     * @return array
     */
    public function findList()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select([
            'd',
//            'i'
        ])
            ->from('AppBundle:Dish', 'd')
            // TODO enable ingredients
//            ->leftJoin('d.ingredients', 'i')
            ->orderBy('d.created', 'DESC')
            ->setMaxResults(100);

        return $qb->getQuery()->getResult();
    }
    public function get($dishId)
    {
        $dish = $this->find($dishId);
        if (!$dish instanceof Dish) {
            throw RuntimeException::notFound('Dish');
        }
        return $dish;
    }
}
