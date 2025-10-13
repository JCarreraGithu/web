<?php

namespace App\Entity;

use App\Repository\LogrosRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LogrosRepository::class)]
class Logros
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'logros')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Empleados $empleado = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 50)]
    private ?string $tipo = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fecha_ocurrencia = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmpleado(): ?Empleados
    {
        return $this->empleado;
    }

    public function setEmpleado(?Empleados $empleado): static
    {
        $this->empleado = $empleado;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getFechaOcurrencia(): ?\DateTime
    {
        return $this->fecha_ocurrencia;
    }

    public function setFechaOcurrencia(\DateTime $fecha_ocurrencia): static
    {
        $this->fecha_ocurrencia = $fecha_ocurrencia;

        return $this;
    }
}
