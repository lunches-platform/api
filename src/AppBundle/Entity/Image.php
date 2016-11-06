<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;
use Swagger\Annotations AS SWG;

/**
 * @Entity(repositoryClass="ImageRepository")
 * @Table(name="image", indexes={
 *     @Index(name="created", columns={"created"})
 * })
 * @SWG\Definition(required={"id","url","format","width","height"}, type="object")
 */
class Image implements \JsonSerializable
{
    /**
     * @var string
     * @Id
     * @GeneratedValue(strategy="NONE")
     * @Column(type="string")
     * @SWG\Property()
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string", length=2094, nullable=false)
     * @SWG\Property()
     */
    protected $url;

    /**
     * @var string
     * @Column(type="string", length=10, nullable=false)
     * @SWG\Property()
     */
    protected $format;

    /**
     * @var int
     * @Column(type="integer", nullable=false)
     * @SWG\Property()
     */
    protected $width;

    /**
     * @var int
     * @Column(type="integer", nullable=false)
     * @SWG\Property()
     */
    protected $height;

    /**
     * @var \DateTimeImmutable $created
     *
     * @Gedmo\Timestampable(on="create")
     * @Column(type="datetime")
     * @SWG\Property()
     */
    private $created;

    /**
     * @var \DateTimeImmutable $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @Column(type="datetime")
     * @SWG\Property()
     */
    private $updated;

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'id' => $this->id,
            'url' => $this->url,
            'format' => $this->format,
            'width' => $this->width,
            'height' => $this->height,
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
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

}
