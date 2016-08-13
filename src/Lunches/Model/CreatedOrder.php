<?php

namespace Lunches\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;

/** @Embeddable */
class CreatedOrder
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * @var \DateTime
     * @Column(type="datetime")
     */
    protected $at;

    public function __construct(Order $order, \DateTime $at)
    {
        $this->order = $order;
        $this->at = $at;
    }

    public function toArray()
    {
        if ($this->at === null) {
            return null;
        }
        return [
            'at' => $this->at->format('Y-m-d H:i:s'),
        ];
    }
}