<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Entity(repositoryClass="DishImageRepository")
 * @Table(name="dish_image", indexes={
 *     @Index(name="created", columns={"created"})
 * })
 */
class DishImage implements \JsonSerializable
{
    /**
     * @var string
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     */
    protected $id;

    /**
     * @var Image
     * @ManyToOne(targetEntity="Image")
     */
    protected $image;
    /**
     * @var bool
     * @Column(type="boolean", name="is_cover")
     */
    protected $isCover;

    /**
     * @var Dish
     * @ManyToOne(targetEntity="AppBundle\Entity\Dish", inversedBy="images")
     */
    protected $dish;
    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @Column(type="datetime")
     */
    private $created;
    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @Column(type="datetime")
     */
    private $updated;

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
//            'id' => $this->id,
            'url' => cloudinary_url($this->getImage()->getId(), array('quality' => 'auto:eco')),
            'isCover' => $this->isCover(),
//            'image' => $this->image,
//            'created' => $this->created->format('Y-m-d H:i:s'),
//            'updated' => $this->updated->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTime $created
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * @return Image
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param Image $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    /**
     * @return boolean
     */
    public function isCover()
    {
        return $this->isCover;
    }

    public function resetCover()
    {
        $this->isCover = false;
    }

    /**
     * @param boolean $isCover
     */
    public function setIsCover($isCover)
    {
        foreach($this->dish->getImages() as $dishImage) {
            if ($dishImage === $this) {
                continue;
            }
            $dishImage->resetCover();
        }
        $this->isCover = $isCover;
    }

    /**
     * @return Dish
     */
    public function getDish()
    {
        return $this->dish;
    }

    /**
     * @param Dish $dish
     */
    public function setDish($dish)
    {
        $this->dish = $dish;
    }

}
