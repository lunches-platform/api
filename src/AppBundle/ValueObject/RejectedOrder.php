<?php

namespace AppBundle\ValueObject;

use AppBundle\Entity\Order;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use AppBundle\Exception\ValidationException;
use Swagger\Annotations AS SWG;

/**
 * @Embeddable
 * @SWG\Definition
 */
class RejectedOrder
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * Date and time when order was rejected
     *
     * @var \DateTimeImmutable
     * @Column(type="datetime", nullable=true)
     * @SWG\Property
     */
    protected $at;
    /**
     * The reason why the order was rejected
     *
     * @var string
     * @Column(type="string", nullable=true)
     * @SWG\Property
     */
    protected $reason;

    public function __construct(Order $order, \DateTimeImmutable $at, $reason)
    {
        $this->order = $order;
        $this->at = $at;
        $this->setReason($reason);
    }

    public function toArray()
    {
        if ($this->at === null && $this->reason === null) {
            return null;
        }
        return [
            'at' => $this->at->format('Y-m-d H:i:s'),
            'reason' => $this->reason,
        ];
    }

    private function setReason($reason)
    {
        $errMsg = 'Provide valid reject "reason"';
        if (!is_string($reason)) {
            throw ValidationException::invalidOrder($errMsg);
        }
        $len = mb_strlen($reason);
        if ($len === 0 || $len > 150) {
            throw ValidationException::invalidOrder($errMsg);
        }
        $this->reason = $reason;
    }
}