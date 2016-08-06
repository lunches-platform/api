<?php

namespace Lunches\Model;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class OrderRepository.
 */
class OrderRepository extends EntityRepository
{
    public function generateOrderNumber()
    {
        $dql = 'SELECT MAX(o.orderNumber) FROM Lunches\Model\Order o';
        $number = $this->_em->createQuery($dql)->getSingleScalarResult();
        
        return !$number ? 1000 : ++$number;
    }

    /**
     * Order is considered active when it had shipment date greater than current
     *
     * @param User $user
     * @param DateRange $dateRange
     * @return array
     */
    public function findByUser(User $user, DateRange $dateRange = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['o'])
            ->from('Lunches\Model\Order', 'o')
            ->where('o.user = :user')
        ;
        $qb->setParameter('user', $user);

        $this->filterByDateRange($qb, $dateRange);

        return $qb->getQuery()->getResult();
    }

    public function findByUsername($username, DateRange $dateRange = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['o'])
            ->from('Lunches\Model\Order', 'o')
            ->join('o.user', 'u')
            ->where('u.fullname = :username')
        ;
        $qb->setParameter('username', $username);

        $this->filterByDateRange($qb, $dateRange);

        return $qb->getQuery()->getResult();
    }

    private function filterByDateRange(QueryBuilder $qb, $dateRange)
    {
        if ($dateRange instanceof DateRange) {
            $qb->andWhere('o.shipmentDate >= :start');
            $qb->andWhere('o.shipmentDate <= :end');
            $qb->setParameter('start', $dateRange->getStart());
            $qb->setParameter('end', $dateRange->getEnd());
        }
    }
}
