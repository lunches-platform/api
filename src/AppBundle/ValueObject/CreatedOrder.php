<?php

namespace AppBundle\ValueObject;

use AppBundle\Entity\Order;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Swagger\Annotations AS SWG;

/** @Embeddable */
class CreatedOrder
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * Date and time when order was created
     *
     * @var \DateTime
     * @Column(type="datetime")
     * @SWG\Property(property="created_at"),
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