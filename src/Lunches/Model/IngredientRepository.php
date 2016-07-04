<?php

namespace Lunches\Model;

use Doctrine\ORM\EntityRepository;

/**
 * Class IngredientRepository.
 */
class IngredientRepository extends EntityRepository
{

    public function getIngredients(array $filters)
    {
        $productId = (int) $filters['productId'];
        $qb = $this->_em->createQueryBuilder();
        $qb->select('i')
            ->from('Lunches\Model\Ingredient', 'i')
            ->where('i.product = :productId')
            ->orderBy('i.created', 'DESC')
            ->setMaxResults(100);

        $qb->setParameter('productId', $productId);

        return new Ingredients($qb->getQuery()->getResult());
    }
}
