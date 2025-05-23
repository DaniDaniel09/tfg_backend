<?php

namespace App\Entity;

use App\Repository\UnitRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

#[ORM\Entity(repositoryClass: UnitRepository::class)]
class Unit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::JSON)]
    private array $images = [];

    #[ORM\Column(type: Types::JSON)]
    private array $colors = [];

    #[ORM\Column(type: Types::JSON)]
    private array $name = [];

    #[ORM\Column(length: 15)]
    private ?string $rarity = null;

    #[ORM\Column]
    private ?bool $isLegendsLimited = null;

    #[ORM\Column]
    private ?bool $isZenkai = null;

    #[ORM\Column]
    private ?bool $isFusing = null;

    #[ORM\Column]
    private ?bool $isTag = null;

    #[ORM\Column]
    private ?bool $isTransforming = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImages(): array
    {
        return $this->images;
    }

    // For backwards compatibility
    public function getImage(): ?string
    {
        return !empty($this->images) ? $this->images[0] : null;
    }

    public function setImages(array $images): static
    {
        $this->images = $images;
        return $this;
    }

    public function addImage(string $image): static
    {
        if (!in_array($image, $this->images)) {
            $this->images[] = $image;
        }
        return $this;
    }

    public function setImage(string $image): static
    {
        $this->images = [$image]; // For backwards compatibility
        return $this;
    }

    public function getColors(): array
    {
        return $this->colors;
    }

    // For backwards compatibility
    public function getColor(): ?string
    {
        return !empty($this->colors) ? $this->colors[0] : null;
    }

    public function setColors(array $colors): static
    {
        $this->colors = $colors;
        return $this;
    }

    public function addColor(string $color): static
    {
        if (!in_array($color, $this->colors)) {
            $this->colors[] = $color;
        }
        return $this;
    }

    public function setColor(string $color): static
    {
        $this->colors = [$color]; // For backwards compatibility
        return $this;
    }

    public function getName(): array
    {
        return $this->name;
    }

    // For backwards compatibility
    public function getNameAsString(): ?string
    {
        return !empty($this->name) ? $this->name[0] : null;
    }

    public function setName(array $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function addName(string $name): static
    {
        if (!in_array($name, $this->name)) {
            $this->name[] = $name;
        }
        return $this;
    }

    // For backwards compatibility
    public function setNameAsString(string $name): static
    {
        $this->name = [$name];
        return $this;
    }

    public function getRarity(): ?string
    {
        return $this->rarity;
    }

    public function setRarity(string $rarity): static
    {
        $this->rarity = $rarity;

        return $this;
    }

    public function isLegendsLimited(): ?bool
    {
        return $this->isLegendsLimited;
    }

    public function setIsLegendsLimited(bool $isLegendsLimited): static
    {
        $this->isLegendsLimited = $isLegendsLimited;

        return $this;
    }

    public function isZenkai(): ?bool
    {
        return $this->isZenkai;
    }

    public function setIsZenkai(bool $isZenkai): static
    {
        $this->isZenkai = $isZenkai;

        return $this;
    }

    public function isFusing(): ?bool
    {
        return $this->isFusing;
    }

    public function setIsFusing(bool $isFusing): static
    {
        $this->isFusing = $isFusing;

        return $this;
    }

    public function isTag(): ?bool
    {
        return $this->isTag;
    }

    public function setIsTag(bool $isTag): static
    {
        $this->isTag = $isTag;

        return $this;
    }

    public function isTransforming(): ?bool
    {
        return $this->isTransforming;
    }

    public function setIsTransforming(bool $isTransforming): static
    {
        $this->isTransforming = $isTransforming;

        return $this;
    }

    public function toArray(): array
    {
        $unitArray = [
            'id' => $this->id,
            'images' => $this->images,
            'colors' => $this->colors,
            'name' => $this->name,
            'rarity' => $this->rarity,
            'is_legends_limited' => $this->isLegendsLimited,
            'is_zenkai' => $this->isZenkai,
            'is_fusing' => $this->isFusing,
            'is_tag' => $this->isTag,
            'is_transforming' => $this->isTransforming
        ];
        return $unitArray;
    }

    public function fromJson($content): void
    {
        $content = json_decode($content, true);

        // Solo establecer ID si está presente (para edición)
        if (isset($content['id'])) {
            $this->id = $content['id'];
        }

        // Imágenes
        if (isset($content['images'])) {
            $this->images = $content['images'];
        } elseif (isset($content['image'])) {
            $this->images = is_array($content['image']) ? $content['image'] : [$content['image']];
        }

        // Colores
        if (isset($content['colors'])) {
            $this->colors = $content['colors'];
        } elseif (isset($content['color'])) {
            $this->colors = is_array($content['color']) ? $content['color'] : [$content['color']];
        }

        // Nombre(s)
        if (isset($content['name'])) {
            $this->name = is_array($content['name']) ? $content['name'] : [$content['name']];
        }

        if (isset($content['rarity'])) $this->rarity = $content['rarity'];
        if (isset($content['is_legends_limited'])) $this->isLegendsLimited = $content['is_legends_limited'];
        if (isset($content['is_zenkai'])) $this->isZenkai = $content['is_zenkai'];
        if (isset($content['is_fusing'])) $this->isFusing = $content['is_fusing'];
        if (isset($content['is_tag'])) $this->isTag = $content['is_tag'];
        if (isset($content['is_transforming'])) $this->isTransforming = $content['is_transforming'];
    }
}