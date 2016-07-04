<?php

namespace Lunches\Model;

use Doctrine\ORM\EntityRepository;

/**
 * Class ProductRepository.
 */
class ProductRepository extends EntityRepository
{

    /**
     * @return array
     */
    public function getProducts()
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['p', 'i'])
            ->from('Lunches\Model\Product', 'p')
            ->leftJoin('p.ingredients', 'i')
            ->orderBy('p.created', 'DESC')
            ->setMaxResults(100);

        return $qb->getQuery()->getResult();
    }
}
