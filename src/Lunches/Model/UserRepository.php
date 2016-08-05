<?php

namespace Lunches\Model;

use Doctrine\ORM\EntityRepository;

/**
 * UserRepository
 */
class UserRepository extends EntityRepository
{
    /**
     * @param string $fullname
     * @return User
     */
    public function findByUsername($fullname)
    {
        $fullname = (string) $fullname;

        return $this->findOneBy([
            'fullname' => $fullname,
        ]);
    }

    public function findByLikePattern($like)
    {
        $dql = 'SELECT u FROM Lunches\Model\User u WHERE u.fullname LIKE :like';

        return $this->_em->createQuery($dql)->setParameter('like', '%'.$like.'%')->getResult();
    }
}
