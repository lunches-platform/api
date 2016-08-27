<?php

namespace Lunches\Model;

use Doctrine\ORM\EntityRepository;

/**
 * TransactionRepository
 */
class TransactionRepository extends EntityRepository
{
    /**
     * @param User $user
     * @return array
     */
    public function findByUser(User $user)
    {
        $dql = 'SELECT t FROM Lunches\Model\Transaction t WHERE t.user = :user';

        return $this->_em->createQuery($dql)->setParameter('user', $user)->getResult();
    }
}
