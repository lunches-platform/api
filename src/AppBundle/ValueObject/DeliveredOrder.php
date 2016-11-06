<?php

namespace AppBundle\ValueObject;

use AppBundle\Entity\Order;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Embeddable;
use AppBundle\Exception\ValidationException;
use Swagger\Annotations AS SWG;

/**
 * @Embeddable
 * @SWG\Definition
 */
class DeliveredOrder
{
    /**
     * @var Order
     */
    protected $order;
    /**
     * Date and time when order was delivered
     *
     * @var \DateTimeImmutable
     * @Column(type="datetime", nullable=true)
     * @SWG\Property
     */
    protected $at;
    /**
     * Delivery service which carried out the order
     *
     * @var string
     * @Column(type="string", nullable=true)
     * @SWG\Property
     */
    protected $carrier;

    public function __construct(Order $order, \DateTimeImmutable $at, $carrier)
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