<?php

namespace AppBundle\ValueObject;

use AppBundle\Entity\Order;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use Swagger\Annotations AS SWG;

/**
 * @Embeddable
 * @SWG\Definition
 */
class CreatedOrder
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * Date and time when order was created
     *
     * @var \DateTimeImmutable
     * @Column(type="datetime")
     * @SWG\Property
     */
    protected $at;

    public function __construct(Order $order, \DateTimeImmutable $at)
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