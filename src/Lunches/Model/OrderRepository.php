<?php

namespace Lunches\Model;

use Doctrine\ORM\EntityRepository;

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
     * @param string $user
     * @param DateRange $dateRange
     * @return array
     */
    public function findByUser($user, DateRange $dateRange = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['o'])
            ->from('Lunches\Model\Order', 'o')
            ->where('o.customer = :user')
        ;
        $qb->setParameter('user', $user);

        if ($dateRange instanceof DateRange) {
            $qb->andWhere('o.shipmentDate >= :start');
            $qb->andWhere('o.shipmentDate <= :end');
            $qb->setParameter('start', $dateRange->getStart());
            $qb->setParameter('end', $dateRange->getEnd());
        }

        return $qb->getQuery()->getResult();
    }
}
