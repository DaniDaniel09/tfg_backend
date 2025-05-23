<?php

namespace App\Entity;

use App\Repository\ZPowerRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ZPowerRepository::class)]
class ZPower
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'zPower', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $usuario = null;

    #[ORM\Column(nullable: true)]
    private ?array $units = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?User
    {
        return $this->usuario;
    }

    public function setUsuario(User $usuario): static
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getUnits(): ?array
    {
        return $this->units;
    }

    public function setUnits(?array $units): static
    {
        $this->units = $units;
        return $this;
    }

    /**
     * Devuelve la entidad ZPower como array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario_id' => $this->usuario?->getId(),
            'units' => $this->units,
        ];
    }

    /**
     * Establece los datos de ZPower desde JSON.
     *
     */
    public function fromJson($content): void
    {
        $content = is_array($content) ? $content : json_decode($content, true);

        if (isset($content['units']) && is_array($content['units'])) {
            $this->units = $content['units'];
        }
    }
}
