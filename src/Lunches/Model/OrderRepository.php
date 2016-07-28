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
     * @param string $customer
     * @return array
     */
    public function getActiveOrders($customer)
    {
        $dql = 'SELECT o FROM Lunches\Model\Order o WHERE o.shipmentDate >= :date AND o.customer = :customer';

        return $this->_em->createQuery($dql)->setParameters([
            'customer' =>  $customer,
            'date' => new \DateTime(),
        ])->getResult();
    }
}
