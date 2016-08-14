<?php

namespace Lunches\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;
use Lunches\Exception\ValidationException;
use Ramsey\Uuid\Uuid;

/**
 * @Entity(repositoryClass="Lunches\Model\TransactionRepository")
 * @Table(name="transaction", indexes={
 *     @Index(name="created", columns={"created"})
 * })
 */
class Transaction
{
    /**
     * @var string
     * @Id
     * @Column(type="guid")
     */
    protected $id;
    /**
     * @var float
     * @Column(type="float", nullable=false)
     */
    protected $amount;
    /**
     * @var User
     * @ManyToOne(targetEntity="User")
     */
    protected $user;
    /**
     * One of income or outcome
     *
     * @var string
     * @Column(type="string", nullable=false)
     */
    protected $type;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @Column(type="datetime")
     */
    protected $created;

    /**
     * Transaction constructor.
     * @param string $type
     * @param float $amount
     * @param User $user
     * @throws ValidationException
     */
    public function __construct($type, $amount, User $user)
    {
        $this->id = Uuid::uuid4();
        $this->setType($type);
        $this->setAmount($amount);
        $this->user = $user;
        $this->created = new \DateTime();
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
            'user' => $this->user->toArray(),
            'created' => $this->created->format('Y-m-d H:i:s'),
        ];
    }

    private function setAmount($amount)
    {
        $amount = (float) $amount;
        if ($amount === 0) {
            throw ValidationException::invalidTransaction('Amount of transaction can not be zero');
        }
        if ($amount > 100000) {
            throw ValidationException::invalidTransaction('Transaction amount can not be higher than 100 000.00');
        }
        $this->amount = $amount;
    }

    private function setType($type)
    {
        if (empty($type)) {
            throw ValidationException::invalidTransaction('Type of transaction is required');
        }

        if (!in_array($type, ['income', 'outcome'], true)) {
            throw ValidationException::invalidTransaction('Only "income" or "outcome" transaction type is allowed');
        }
        $this->type = $type;
    }

}
