<?php

namespace Lunches\Model;

use Doctrine\ORM\EntityRepository;
use Lunches\Exception\RuntimeException;

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

    public function get($productId)
    {
        $product = $this->find((int) $productId);
        if (!$product instanceof Product) {
            throw RuntimeException::notFound('Product');
        }
        return $product;
    }
}
