<?php

namespace AppBundle\Entity;

use AppBundle\ValueObject\DateRange;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Class OrderRepository.
 */
class OrderRepository extends EntityRepository
{
    public function generateOrderNumber()
    {
        $dql = 'SELECT MAX(o.orderNumber) FROM AppBundle\Entity\Order o';
        $number = $this->_em->createQuery($dql)->getSingleScalarResult();
        
        return !$number ? 1000 : ++$number;
    }

    public function getList(array $filters)
    {
        if (count($filters) === 0) {
            return [];
        }
        $qb = $this->_em->createQueryBuilder();

        $qb->select(['o'])
            ->from('AppBundle\Entity\Order', 'o');

        if (array_key_exists('username', $filters)) {
            $qb->join('o.user', 'u')->andWhere('u.fullname = :username')->setParameter('username', $filters['username']);
        }
        if (array_key_exists('dateRange', $filters)) {
            $this->filterByDateRange($qb, $filters['dateRange']);
        }
        if (array_key_exists('shipmentDate', $filters)) {
            $qb->andWhere('o.shipmentDate = :date')->setParameter('date', $filters['shipmentDate']);
        }
        if (array_key_exists('paid', $filters)) {
            $qb->andWhere('o.payment.status = :paid');
            $qb->setParameter('paid', (int) $filters['paid']);
        }

        return $qb->getQuery()->getResult();
    }

    public function findByShipmentDate(\DateTime $shipmentDate)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['o'])
            ->from('AppBundle\Entity\Order', 'o')
            ->where('o.shipmentDate = :date')
        ;
        $qb->setParameter('date', $shipmentDate);

        return $qb->getQuery()->getResult();
    }

    public function findByUser(User $user, $paid = null, $withCanceled = 0, DateRange $dateRange = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['o'])
            ->from('AppBundle\Entity\Order', 'o')
            ->join('o.user', 'u')
            ->where('u.id = :userId')
            ->orderBy('o.shipmentDate', 'DESC')
        ;
        if ((int) $withCanceled === 0) {
            $qb->andWhere("o.status != 'canceled'");
        }
        $qb->setParameter('userId', $user);

        if ($paid !== null) {
            $qb->andWhere('o.payment.status = :paid');
            $qb->setParameter('paid', (int) $paid);
        }

        $this->filterByDateRange($qb, $dateRange);

        return $qb->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @return Order[]
     */
    public function findNonPaidOrders(User $user)
    {
        $dql = "SELECT o FROM \AppBundle\Entity\Order o WHERE o.payment.status = 0 AND o.status NOT IN('canceled', 'rejected') AND o.user = :user ORDER BY o.createdOrder.at ASC";

        return $this->_em->createQuery($dql)->setParameter('user', $user)->getResult();
    }

    /**
     * @return Order[]
     */
    public function findPaidAndDelivered()
    {
        $dql = "SELECT o FROM \AppBundle\Entity\Order o WHERE o.payment.status = 1 AND o.status = 'delivered'";

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
