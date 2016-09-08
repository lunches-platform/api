<?php

namespace Lunches\Model;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Lunches\Exception\ValidationException;

/** @Embeddable */
class RejectedOrder
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var \DateTime
     * @Column(type="datetime", nullable=true)
     */
    protected $at;
    /**
     * @var string
     * @Column(type="string", nullable=true)
     */
    protected $reason;

    public function __construct(Order $order, \DateTime $at, $reason)
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