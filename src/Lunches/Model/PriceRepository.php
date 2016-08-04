<?php

namespace Lunches\Model;

use Doctrine\ORM\EntityRepository;

/**
 * PriceRepository
 */
class PriceRepository extends EntityRepository
{
    public function findByDate(\DateTime $date)
    {
        $prices = $this->findBy([
            'date' => $date,
        ]);

        return new Prices($prices);
    }
}
