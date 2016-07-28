<?php

namespace Lunches\Model;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use Lunches\Exception\ValidationException;
use Ramsey\Uuid\Uuid;


/**
 * Class Price
 * @Entity(repositoryClass="PriceRepository")
 * @Table(name="price")
 */
class Price
{
    /**
     * @var Uuid
     *
     * @Id
     * @Column(type="guid")
     */
    protected $id;
    /**
     * @var float
     * @Column(type="float")
     */
    protected $value;
    /**
     * @var PriceItem[]
     * @OneToMany(targetEntity="PriceItem", mappedBy="price", cascade={"persist"})
     */
    protected $items;
    /**
     * @var \DateTime
     * @Column(type="date")
     */
    protected $date;

    public function __construct($value, \DateTime $date, $items)
    {
        $this->id = Uuid::uuid4();
        $this->setDate($date);
        $this->setItems($items);
        $this->setValue($value);
    }

    private function setValue($value)
    {
        $value = (float) $value;
        if (!$value) {
            throw ValidationException::invalidPrice();
        }
        $this->value = $value;
    }

    private function setItems(array $items)
    {
        $this->items = [];
        foreach ($items as $item) {
            $this->items[] = $this->createItem($item);
        }

        if (count($this->items) === 0) {
            throw ValidationException::invalidPrice('Price must be assigned to one or more products');
        }
    }

    private function createItem(array $item)
    {
        if (!array_key_exists('size', $item)) {
            throw ValidationException::invalidPrice('Price item must have "size" of product specified');
        }

        if (!array_key_exists('product', $item)) {
            throw ValidationException::invalidPrice('Price item must contain valid product');
        }

        return new PriceItem($this, $item['product'], $item['size']);
    }

    private function setDate(\DateTime $date)
    {
        $currentDate = new \DateTime((new \DateTime())->format('Y-m-d')); // remove time part
        if ($date <= $currentDate) {
            throw ValidationException::invalidDate('Price date can not be today or in the past');
        }
        
        $this->date = $date;
    }

}