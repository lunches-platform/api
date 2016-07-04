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
        $dql = 'SELECT MAX(o.number) FROM Lunches\Model\Order o';
        $number = $this->_em->createQuery($dql)->getSingleScalarResult();
        
        return !$number ? 1000 : ++$number;
    }
}
