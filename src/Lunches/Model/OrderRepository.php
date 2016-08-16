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

    public function findByShipmentDate(\DateTime $shipmentDate)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['o'])
            ->from('Lunches\Model\Order', 'o')
            ->where('o.shipmentDate = :date')
        ;
        $qb->setParameter('date', $shipmentDate);

        return $qb->getQuery()->getResult();
    }

    public function findByUsername($username, $paid = null, $withCanceled = 0, DateRange $dateRange = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['o'])
            ->from('Lunches\Model\Order', 'o')
            ->join('o.user', 'u')
            ->where('u.fullname = :username')
        ;
        if ((int) $withCanceled === 0) {
            $qb->andWhere("o.status != 'canceled'");
        }
        $qb->setParameter('username', $username);

        if ($paid !== null) {
            $qb->andWhere('o.paid = :paid');
            $qb->setParameter('paid', (int) $paid);
        }

        $this->filterByDateRange($qb, $dateRange);

        return $qb->getQuery()->getResult();
    }

    public function findCreatedOrders()
    {
        $dql = "SELECT o FROM \Lunches\Model\Order o WHERE o.status = 'created'";

        return $this->_em->createQuery($dql)->iterate();
    }

    /**
     * @return Order[]
     */
    public function findNonPaidOrders()
    {
        $dql = "SELECT o FROM \Lunches\Model\Order o WHERE o.paid = 0";

        return $this->_em->createQuery($dql)->getResult();
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
