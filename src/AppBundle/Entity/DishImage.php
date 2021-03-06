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
use Swagger\Annotations as SWG;

/**
 * @Entity(repositoryClass="DishImageRepository")
 * @Table(name="dish_image", indexes={
 *     @Index(name="created", columns={"created"})
 * })
 * @SWG\Definition(required={"image","dish"}, type="object")
 */
class DishImage implements \JsonSerializable
{
    /**
     * @var string
     * @Id
     * @GeneratedValue
     * @Column(type="integer")
     * @SWG\Property(readOnly=true)
     */
    protected $id;

    /**
     * @var Image
     * @ManyToOne(targetEntity="Image")
     * @SWG\Property
     */
    protected $image;
    /**
     * @var bool
     * @Column(type="boolean", name="is_cover")
     * @SWG\Property()
     */
    protected $isCover;

    /**
     * @var Dish
     * @ManyToOne(targetEntity="AppBundle\Entity\Dish", inversedBy="images")
     * @SWG\Property
     */
    protected $dish;
    /**
     * @var \DateTimeImmutable $created
     *
     * @Gedmo\Timestampable(on="create")
     * @Column(type="datetime")
     * @SWG\Property(readOnly=true)
     */
    private $created;
    /**
     * @var \DateTimeImmutable $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @Column(type="datetime")
     * @SWG\Property(readOnly=true)
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
     * @return \DateTimeImmutable
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTimeImmutable $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTimeImmutable $created
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
