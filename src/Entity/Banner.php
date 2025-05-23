<?php

namespace App\Entity;

use App\Repository\BannerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BannerRepository::class)]
class Banner
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $banner_name = null;

    #[ORM\Column(length: 100)]
    private ?string $banner_type = null;

    #[ORM\Column]
    private array $units = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBannerName(): ?string
    {
        return $this->banner_name;
    }

    public function setBannerName(string $banner_name): static
    {
        $this->banner_name = $banner_name;

        return $this;
    }

    public function getBannerType(): ?string
    {
        return $this->banner_type;
    }

    public function setBannerType(string $banner_type): static
    {
        $this->banner_type = $banner_type;

        return $this;
    }

    public function getUnits(): array
    {
        return $this->units;
    }

    public function setUnits(array $units): static
    {
        $this->units = $units;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'banner_name' => $this->banner_name,
            'banner_type' => $this->banner_type,
            'units' => $this->units,
        ];
    }

    public function fromJson($content): void
    {
        $content = json_decode($content, true);
        $this->id = $content['id'];
        $this->banner_name = $content['banner_name'];
        $this->banner_type = $content['banner_type'];
        $this->units = $content['units'];
    }

}
