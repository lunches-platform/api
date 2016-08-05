<?php

namespace Lunches\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;
use Lunches\Exception\ValidationException;
use Ramsey\Uuid\Uuid;

/**
 * @Entity(repositoryClass="Lunches\Model\UserRepository")
 * @Table(name="user", indexes={
 *     @Index(name="created", columns={"created"})
 * })
 */
class User
{
    /**
     * @var string
     * @Id
     * @Column(type="guid")
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string", length=255, nullable=false)
     */
    protected $fullname;
    /**
     * @var float
     * @Column(type="float", nullable=false)
     */
    protected $balance;
    /**
     * @var float
     * @Column(type="string", nullable=false)
     */
    protected $address;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @Column(type="datetime")
     */
    protected $created;

    /**
     * Product constructor.
     * @param $name
     */
    public function __construct($name)
    {
        $this->id = Uuid::uuid4();
        if (!is_string($name)) {
            ValidationException::invalidUser('username must have string data type');
        }
        $this->fullname = $name;
        $this->created = new \DateTime();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'fullname' => $this->fullname,
        ];
    }
}
