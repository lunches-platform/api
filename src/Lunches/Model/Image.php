<?php

namespace Lunches\Model;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\Table;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Entity(repositoryClass="ImageRepository")
 * @Table(name="image", indexes={
 *     @Index(name="created", columns={"created"})
 * })
 */
class Image
{
    /**
     * @var string
     * @Id
     * @GeneratedValue(strategy="NONE")
     * @Column(type="string")
     */
    protected $id;

    /**
     * @var string
     * @Column(type="string", length=2094, nullable=false)
     */
    protected $url;

    /**
     * @var string
     * @Column(type="string", length=10, nullable=false)
     */
    protected $format;

    /**
     * @var int
     * @Column(type="integer", nullable=false)
     */
    protected $width;

    /**
     * @var int
     * @Column(type="integer", nullable=false)
     */
    protected $height;

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
    public function toArray()
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
