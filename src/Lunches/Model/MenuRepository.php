<?php

namespace Lunches\Model;

use Doctrine\ORM\EntityRepository;

/**
 * Class MenuRepository.
 */
class MenuRepository extends EntityRepository
{
    /**
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @return Menu[]
     */
    public function getMenus(\DateTime $startDate = null, \DateTime $endDate = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['m', 'mp', 'p', 'i'])
            ->from('Lunches\Model\Menu', 'm')
            ->join('m.menuProducts', 'mp')
            ->join('mp.product', 'p')
            ->leftJoin('p.ingredients', 'i')
//            ->orderBy('m.created', 'DESC')
            ->setMaxResults(100);

        if ($startDate && !$endDate) {
            $qb->where('m.date = :date');
            $qb->setParameter('date', $startDate->format('Y-m-d'));
        }

        if ($startDate && $endDate) {
            $qb->where('m.date >= :start');
            $qb->andWhere('m.date <= :end');
            $qb->setParameters([
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ]);
        }

        return $qb->getQuery()->getResult();
    }
}