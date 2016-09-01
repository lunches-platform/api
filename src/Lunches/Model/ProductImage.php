<?php

namespace Lunches\Model;

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
 * @Entity
 * @Table(name="product_image", indexes={
 *     @Index(name="created", columns={"created"})
 * })
 */
class ProductImage
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
     * @var Product
     * @ManyToOne(targetEntity="Product", inversedBy="images")
     */
    protected $product;
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
     * @param bool $short
     * @return array
     */
    public function toArray($short = false)
    {
        if ($short === true) {
            return [
                'url' => cloudinary_url($this->getImage()->getId(), array('quality' => 'auto:eco')),
                'isCover' => $this->isCover(),
            ];
        }

        return [
            'id' => $this->id,
            'image' => $this->image->toArray(),
            'product' => $this->product->toArray(),
            'isCover' => $this->isCover,
            'created' => $this->created->format('Y-m-d H:i:s'),
            'updated' => $this->updated->format('Y-m-d H:i:s'),
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
        foreach($this->product->getImages() as $productImage) {
            if ($productImage === $this) {
                continue;
            }
            $productImage->resetCover();
        }
        $this->isCover = $isCover;
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @param Product $product
     */
    public function setProduct($product)
    {
        $this->product = $product;
    }

}
