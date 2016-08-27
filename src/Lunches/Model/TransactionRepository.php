<?php

namespace Lunches\Model;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * TransactionRepository
 */
class TransactionRepository extends EntityRepository
{

    public function findByUser(User $user, DateRange $dateRange = null, $type = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['t'])
            ->from('Lunches\Model\Transaction', 't')
            ->where('t.user = :user')
            ->orderBy('t.created', 'DESC')
        ;
        $qb->setParameter('user', $user);

        if ($type !== null) {
            $qb->andWhere('t.type = :type');
            $qb->setParameter('type', $type);
        }

        $this->filterByDateRange($qb, $dateRange);

        return $qb->getQuery()->getResult();
    }

    private function filterByDateRange(QueryBuilder $qb, $dateRange)
    {
        if ($dateRange instanceof DateRange) {
            $qb->andWhere('t.created >= :start');
            $qb->andWhere('t.created <= :end');
            $qb->setParameter('start', $dateRange->getStart());
            $qb->setParameter('end', $dateRange->getEnd());
        }
    }
}
