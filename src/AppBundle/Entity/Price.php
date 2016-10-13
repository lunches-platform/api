<?php

namespace AppBundle\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\Table;
use AppBundle\Exception\ValidationException;
use Ramsey\Uuid\Uuid;
use Swagger\Annotations as SWG;


/**
 * Class Price
 * @Entity(repositoryClass="AppBundle\Entity\PriceRepository")
 * @Table(name="price")
 * @SWG\Definition(required={"value","date","items"}, type="object")
 */
class Price implements \JsonSerializable
{
    /**
     * @var Uuid
     *
     * @Id
     * @Column(type="guid")
     * @SWG\Property(readOnly=true)
     */
    protected $id;
    /**
     * @var float
     * @Column(type="float")
     * @SWG\Property()
     */
    protected $value;
    /**
     * @var PriceItem[]
     * @OneToMany(targetEntity="PriceItem", mappedBy="price", cascade={"persist"})
     * @SWG\Property
     */
    protected $items;
    /**
     * @var \DateTime
     * @Column(type="date")
     * @SWG\Property()
     */
    protected $date;

    public function __construct($value, \DateTime $date)
    {
        $this->id = Uuid::uuid4();
        $this->setDate($date);
        $this->setValue($value);
        $this->items = new ArrayCollection();
    }

    public function hasPriceItem(PriceItem $priceItem)
    {
        foreach ($this->items as $item) {
            if ($item->equalsTo($priceItem)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return float
     * @throws ValidationException
     */
    public function getValue()
    {
        if (count($this->items) === 0) {
            throw ValidationException::invalidPrice('Price must be assigned to one or more items but zero given. Probably some of items are invalid');
        }
        return $this->value;
    }
    public function setItems(array $items)
    {
        array_map([$this, 'addItem'], $items);
    }

    /**
     * @return ArrayCollection
     */
    public function getItems()
    {
        return $this->items;
    }

    private function setValue($value)
    {
        $value = (float) $value;
        if (!$value) {
            throw ValidationException::invalidPrice();
        }
        $this->value = $value;
    }

    private function addItem(PriceItem $priceItem)
    {
        $this->items[] = $priceItem;
    }


    private function setDate(\DateTime $date)
    {
        $currentDate = new \DateTime((new \DateTime())->format('Y-m-d')); // remove time part
        if ($date <= $currentDate) {
            throw ValidationException::invalidDate('Price date can not be today or in the past');
        }
        
        $this->date = $date;
    }

    public function equalsTo(Price $price)
    {
        if ($this->value !== $price->getValue()) {
            return false;
        }
        if ($this->date != $price->getDate()) {
            return false;
        }

        return $this->areItemsEquals($price->getItems());
    }
    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    public function toArray()
    {
        return [
            'date' => $this->date->format('Y-m-d'),
            'price' => $this->value,
            'items' => $this->items,
        ];
    }

    /**
     * @param ArrayCollection $items
     * @return bool
     */
    public function areItemsEquals($items)
    {
        if ($this->items->count() !== $items->count()) {
            return false;
        }

        foreach ($this->items as $priceItem) {
            $equals = 0;
            foreach ($items as $currentPriceItem)  {
                if ($priceItem->equalsTo($currentPriceItem)) {
                    $equals++;
                    break;
                }
            }
            if (!$equals || $equals > 1) {
                return false;
            }
        }
        
        return true;
    }
}