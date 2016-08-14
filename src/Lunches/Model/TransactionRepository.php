<?php

namespace Lunches\Model;

use Doctrine\ORM\EntityRepository;

/**
 * TransactionRepository
 */
class TransactionRepository extends EntityRepository
{
    /**
     * @param string $user
     * @return array
     */
    public function findByUser($user)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['t'])
            ->from('Lunches\Model\Transaction', 't')
            ->where('t.user = :user')
        ;
        $qb->setParameter('user', $user);

        return $qb->getQuery()->getResult();
    }
}
