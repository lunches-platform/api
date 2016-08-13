<?php

namespace Lunches\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Lunches\Exception\ValidationException;

/** @Embeddable */
class DeliveredOrder
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
    protected $carrier;

    public function __construct(Order $order, \DateTime $at, $carrier)
    {
        $this->order = $order;
        $this->at = $at;
        $this->setCarrier($carrier);
    }

    public function toArray()
    {
        if ($this->at === null && $this->carrier === null) {
            return null;
        }
        return [
            'at' => $this->at->format('Y-m-d H:i:s'),
        ];
    }

    private function setCarrier($carrier)
    {
        $errMsg = 'Provide valid carrier';
        if (!is_string($carrier)) {
            ValidationException::invalidOrder($errMsg);
        }
        $len = mb_strlen($carrier);
        if ($len < 4 || $len > 150) {
            throw ValidationException::invalidOrder($errMsg);
        }
        $this->carrier = $carrier;
    }
}