<?php

namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * Class MenuRepository.
 */
class MenuRepository extends EntityRepository
{
    /**
     * @param \DateTimeImmutable $startDate
     * @param \DateTimeImmutable $endDate
     * @return Menu[]
     */
    public function getMenus(\DateTimeImmutable $startDate = null, \DateTimeImmutable $endDate = null)
    {
        $qb = $this->_em->createQueryBuilder();
        $qb->select(['m', 'md', 'd', 'i'])
            ->from('AppBundle:Menu', 'm')
            ->join('m.menuDishes', 'md')
            ->join('md.dish', 'd')
            ->leftJoin('d.ingredients', 'i')
            ->orderBy('m.date', 'ASC')
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

    /**
     * @param \DateTimeImmutable|string $date
     * @return Menu[]
     */
    public function findByDate($date)
    {
        return $this->findBy([
            'date' => $date,
        ]);
    }

    public function exists(\DateTimeImmutable $date, $type)
    {
        return (bool) $this->findOneBy([
            'date' => $date,
            'type' => $type,
        ]);
    }
}