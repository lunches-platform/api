<?php

namespace AppBundle\ValueObject;

use AppBundle\Entity\Order;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use AppBundle\Exception\ValidationException;
use Swagger\Annotations AS SWG;

/** @Embeddable */
class CanceledOrder
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * Date and time when order was canceled
     *
     * @var \DateTime
     * @Column(type="datetime", nullable=true)
     * @SWG\Property(property="canceled_at"),
     */
    protected $at;
    /**
     * The reason why the order was canceled
     *
     * @var string
     * @Column(type="string", nullable=true)
     * @SWG\Property(property="canceled_reason"),
     */
    protected $reason;

    public function __construct(Order $order, \DateTime $at, $reason = '')
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
        $errMsg = 'Provide valid cancel "reason"';
        if (!is_string($reason)) {
            throw ValidationException::invalidOrder($errMsg);
        }
        $len = mb_strlen($reason);
        if ($len > 150) {
            throw ValidationException::invalidOrder($errMsg);
        }
        $this->reason = $reason;
    }
}