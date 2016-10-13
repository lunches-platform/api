<?php

namespace AppBundle\Entity;

use AppBundle\Exception\UserException;
use AppBundle\Exception\ValidationException;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;

/**
 * @Entity(repositoryClass="AppBundle\Entity\UserRepository")
 * @Table(name="user", indexes={
 *     @Index(name="created", columns={"created"})
 * })
 * @SWG\Definition(required={"username","address"}, type="object")
 */
class User implements \JsonSerializable
{
    /**
     * @var string
     * @Id
     * @Column(type="guid")
     * @SWG\Property(readOnly=true)
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string", name="full_name", length=255, nullable=false)
     * @SWG\Property(property="username")
     */
    protected $fullname;
    /**
     * Current user balance. It updates automatically with every completed transaction
     *
     * @var float
     * @Column(type="float", nullable=false)
     * @SWG\Property(readOnly=true)
     */
    protected $balance;
    /**
     * User can have credit money. It increases when user have insufficient funds to pay for an order, and decreases when order is paid successfully
     *
     * @var float
     * @Column(type="float", nullable=true)
     * @SWG\Property(readOnly=true)
     */
    protected $credit = 0;
    /**
     * Auto generated human readable user ID
     * @var int
     * @Column(type="integer", name="client_id", nullable=false)
     * @SWG\Property(readOnly=true)
     */
    protected $clientId;
    /**
     * Default user address. It will be used, when there were no address specified for an Order
     * @var string
     * @Column(type="string", nullable=false)
     * @SWG\Property()
     */
    protected $address;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @Column(type="datetime")
     * @SWG\Property(readOnly=true)
     */
    protected $created;

    /**
     * User constructor.
     * @param int $clientId
     * @param string $name
     * @param string $address
     * @throws ValidationException
     */
    public function __construct($clientId, $name, $address)
    {
        $this->id = Uuid::uuid4();
        $this->clientId = $clientId;
        $this->setUsername($name);
        $this->setAddress($address);
        $this->created = new \DateTime();
        $this->balance = 0;
        $this->credit = 0;
    }
    public function changeAddress($address)
    {
        $this->setAddress($address);
    }

    public function rechargeBalance($amount)
    {
        $this->balance += (float) $amount;
    }

    public function chargeBalance($amount)
    {
        if ($this->balance >= $amount) {
            $this->balance -= (float) $amount;
        } else {
            throw UserException::insufficientFunds();
        }
    }
    public function payCredit($price)
    {
        $this->credit -= $price;
        if ($this->credit < 0) {
            $this->credit = 0;
        }
    }
    public function takeCredit($price)
    {
        $this->credit += $price;
    }

    public function currentCredit()
    {
        return $this->credit;
    }

    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'clientId' => $this->clientId,
            'fullname' => $this->fullname,
            'address' => $this->address,
            'balance' => $this->balance,
            'credit' => $this->credit,
        ];
    }

    public function getAddress()
    {
        return $this->address;
    }

    private function setUsername($name)
    {
        // TODO use Assertion lib
        if (empty($name)) {
            throw ValidationException::invalidUser('Username is required');
        }
        if (!is_string($name)) {
            throw ValidationException::invalidUser('Username must have string data type');
        }
        $len = mb_strlen($name);
        if ($len <= 2 || $len > 50) {
            throw ValidationException::invalidUser('Length of user name must be greater than 2 and less than 50');
        }
        $this->fullname = $name;
    }

    private function setAddress($address)
    {
        // TODO use Assertion lib
        if (empty($address)) {
            throw ValidationException::invalidUser('Address is required');
        }
        $len = mb_strlen($address);
        if ($len < 1 || $len > 150) {
            throw ValidationException::invalidUser('Address must be greater than zero and less than 150 characters');
        }
        $this->address = $address;
    }

}
