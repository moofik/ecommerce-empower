<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use JMS\Serializer\Annotation as Serializer;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;


/**
 * @ORM\Entity(repositoryClass="App\Repository\ItemRepository")
 * @Serializer\ExclusionPolicy("all")
 */
class Item
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Serializer\Expose()
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=500)
     * @Serializer\Expose()
     */
    private $description;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Serializer\Expose()
     * @Serializer\SerializedName("priceType")
     */
    private $priceType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Expose()
     * @Serializer\SerializedName("priceMin")
     */
    private $priceMin;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Expose()
     * @Serializer\SerializedName("priceMax")
     */
    private $priceMax;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Expose()
     * @Serializer\SerializedName("isBargainPossible")
     */
    private $isBargainPossible;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * @Serializer\Expose()
     * @Serializer\SerializedName("isExchangePossible")
     */
    private $isExchangePossible;

    /**
     * @ORM\Column(type="uuid")
     * @Serializer\Expose()
     */
    private $uuid;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Serializer\Expose()
     */
    private $quantity;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", length=100)
     */
    private $slug;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Tag", mappedBy="items")
     * @Serializer\Expose()
     */
    private $tags;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Shop", inversedBy="items")
     * @ORM\JoinColumn(nullable=false)
     * @Serializer\Expose()
     * @Serializer\Groups({"shop"})
     */
    private $shop;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->uuid = Uuid::uuid4();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPriceType(): ?string
    {
        return $this->priceType;
    }

    public function setPriceType(?string $priceType): self
    {
        $this->priceType = $priceType;

        return $this;
    }

    public function getPriceMin(): ?int
    {
        return $this->priceMin;
    }

    public function setPriceMin(?int $priceMin): self
    {
        $this->priceMin = $priceMin;

        return $this;
    }

    public function getPriceMax(): ?int
    {
        return $this->priceMax;
    }

    public function setPriceMax(?int $priceMax): self
    {
        $this->priceMax = $priceMax;

        return $this;
    }

    public function getIsBargainPossible(): ?bool
    {
        return $this->isBargainPossible;
    }

    public function setIsBargainPossible(?bool $isBargainPossible): self
    {
        $this->isBargainPossible = $isBargainPossible;

        return $this;
    }

    public function getIsExchangePossible(): ?bool
    {
        return $this->isExchangePossible;
    }

    public function setIsExchangePossible(?bool $isExchangePossible): self
    {
        $this->isExchangePossible = $isExchangePossible;

        return $this;
    }

    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    public function setUuid($uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(?int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return Collection|Tag[]
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;
            $tag->addItem($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        if ($this->tags->contains($tag)) {
            $this->tags->removeElement($tag);
            $tag->removeItem($this);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    public function getShop(): Shop
    {
        return $this->shop;
    }

    public function setShop(Shop $shop): self
    {
        $this->shop = $shop;

        return $this;
    }
}
