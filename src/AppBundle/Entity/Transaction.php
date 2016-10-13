<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;
use AppBundle\Exception\ValidationException;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;

/**
 * @Entity(repositoryClass="TransactionRepository")
 * @Table(name="transaction", indexes={
 *     @Index(name="created", columns={"created"})
 * })
 * @SWG\Definition(required={"type","amount","user"}, type="object")
 */
class Transaction implements \JsonSerializable
{
    const TYPE_INCOME = 'income';
    const TYPE_OUTCOME = 'outcome';
    const TYPE_REFUND = 'refund';
    /**
     * @var string
     * @Id
     * @Column(type="guid")
     * @SWG\Property(readOnly=true)
     */
    protected $id;
    /**
     * @var float
     * @Column(type="float", nullable=false)
     * @SWG\Property()
     */
    protected $amount;
    /**
     * @var User
     * @ManyToOne(targetEntity="User")
     * @SWG\Property
     */
    protected $user;
    /**
     * @var string
     * @Column(type="string", nullable=false)
     * @SWG\Property(
     *     enum={"income","outcome","refund"},
     * )
     */
    protected $type;

    /**
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     * @Column(type="datetime")
     * @SWG\Property(readOnly=true)
     */
    protected $created;

    /**
     * @var \DateTime
     * @Column(type="datetime", name="payment_date")
     * @SWG\Property()
     */
    protected $paymentDate;

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
        $this->paymentDate = new \DateTime();
        $this->updateUserBalance();
    }

    public function paidAt($paymentDate)
    {
        if (!$paymentDate instanceof \DateTime) {
            try {
                $paymentDate = new \DateTime($paymentDate);
            } catch (\Exception $e) {
                throw ValidationException::invalidTransaction('Payment date has invalid format');
            }
        }
        $this->paymentDate = $paymentDate;
    }

    public function type()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'amount' => $this->amount,
//            'user' => $this->user->toArray(),
            'created' => $this->created->format('Y-m-d H:i:s'),
        ];
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function paymentDate()
    {
        return $this->paymentDate;
    }
    private function updateUserBalance()
    {
        if ($this->type === self::TYPE_INCOME || $this->type === self::TYPE_REFUND) {
            $this->user->rechargeBalance($this->amount);
        }
        if ($this->type === self::TYPE_OUTCOME) {
            $this->user->chargeBalance($this->amount);
        }
    }

    private function setAmount($amount)
    {
        $amount = (float) $amount;
        if ($amount <= 0.0) {
            throw ValidationException::invalidTransaction('Amount of transaction can not be negative or zero');
        }
        if ($amount > 100000) {
            throw ValidationException::invalidTransaction('Transaction amount can not be higher than 100 000.00');
        }
        $this->amount = $amount;
    }

    private function setType($type)
    {
        if (empty($type)) {
            throw ValidationException::invalidTransaction('Type of transaction is required.');
        }

        if (!in_array($type, [self::TYPE_INCOME, self::TYPE_OUTCOME, self::TYPE_REFUND], true)) {
            throw ValidationException::invalidTransaction('Only "income", "outcome" or "refund" transaction type is allowed');
        }
        $this->type = $type;
    }

}
