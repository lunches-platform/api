<?php

namespace AppBundle\Entity;

use AppBundle\ValueObject\DateRange;
use Doctrine\Common\Collections\ArrayCollection;
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
        $filters = array_filter($filters, function ($item) {
            return $item !== null && $item !== '';
        });
        if (count($filters) === 0) {
            return [];
        }
        $qb = $this->_em->createQueryBuilder();

        $qb->select(['o'])
            ->from('AppBundle\Entity\Order', 'o')
            ->orderBy('o.shipmentDate', 'DESC');

        if (array_key_exists('username', $filters)) {
            $qb->join('o.user', 'u')->andWhere('u.fullname = :username')->setParameter('username', $filters['username']);
        }
        if (array_key_exists('user', $filters)) {
            $qb->join('o.user', 'u')->andWhere('u.id = :userId')->setParameter('userId', $filters['user']);
        }
        $dateRangeExists = array_key_exists('dateRange', $filters);
        $shipmentDateExists = array_key_exists('shipmentDate', $filters);

        // dateRange and shipmentDate are exclusive parameters
        if ($dateRangeExists || $shipmentDateExists) {
            if ($shipmentDateExists) {
                $qb->andWhere('o.shipmentDate = :date')->setParameter('date', $filters['shipmentDate']);
            } else {
                $this->filterByDateRange($qb, $filters['dateRange']);
            }
        }
        if (array_key_exists('paid', $filters) && $filters['paid'] !== null) {
            $qb->andWhere('o.payment.status = :paid')->setParameter('paid', (int) $filters['paid']);
        }
        if (array_key_exists('withCanceled', $filters) && $filters['withCanceled'] !== null && (int) $filters['withCanceled'] === 0) {
            $qb->andWhere("o.status != 'canceled'");
        }

        $orders = $qb->getQuery()->getResult();

        if (array_key_exists('items', $filters) && $filters['items'] instanceof ArrayCollection) {
            $items = $filters['items'];
            $orders = array_filter($orders, function (Order $order) use ($items) {
                return $order->areItemsEquals($items);
            });
        }

        return array_values($orders);
    }

    public function findByShipmentDate(\DateTimeImmutable $shipmentDate)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['o'])
            ->from('AppBundle\Entity\Order', 'o')
            ->where('o.shipmentDate = :date')
        ;
        $qb->setParameter('date', $shipmentDate);

        return $qb->getQuery()->getResult();
    }

    public function findByUser(User $user, array $filters = [])
    {
        $filters['user'] = $user;

        return $this->getList($filters);
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
