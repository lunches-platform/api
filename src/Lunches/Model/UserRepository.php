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
}
