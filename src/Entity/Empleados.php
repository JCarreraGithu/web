<?php

namespace App\Entity;

use App\Repository\EmpleadosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmpleadosRepository::class)]
class Empleados
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $nombre = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $apellido = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTime $fecha_nacimiento = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fotografia = null;

    #[ORM\ManyToOne(inversedBy: 'empleados')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Puestos $puesto = null;

    #[ORM\ManyToOne(inversedBy: 'empleados')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tiendas $tienda = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $salario = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;

    /**
     * @var Collection<int, Logros>
     */
    #[ORM\OneToMany(targetEntity: Logros::class, mappedBy: 'empleado', orphanRemoval: true)]
    private Collection $logros;

    public function __construct()
    {
        $this->logros = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(?string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    public function getApellido(): ?string
    {
        return $this->apellido;
    }

    public function setApellido(?string $apellido): static
    {
        $this->apellido = $apellido;

        return $this;
    }

    public function getFechaNacimiento(): ?\DateTime
    {
        return $this->fecha_nacimiento;
    }

    public function setFechaNacimiento(\DateTime $fecha_nacimiento): static
    {
        $this->fecha_nacimiento = $fecha_nacimiento;

        return $this;
    }

    public function getFotografia(): ?string
    {
        return $this->fotografia;
    }

    public function setFotografia(?string $fotografia): static
{
    $this->fotografia = $fotografia;
    return $this;
}


    public function getPuesto(): ?Puestos
    {
        return $this->puesto;
    }

    public function setPuesto(?Puestos $puesto): static
    {
        $this->puesto = $puesto;

        return $this;
    }

    public function getTienda(): ?Tiendas
    {
        return $this->tienda;
    }

    public function setTienda(?Tiendas $tienda): static
    {
        $this->tienda = $tienda;

        return $this;
    }

    public function getSalario(): ?string
    {
        return $this->salario;
    }

    public function setSalario(string $salario): static
    {
        $this->salario = $salario;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    /**
     * @return Collection<int, Logros>
     */
    public function getLogros(): Collection
    {
        return $this->logros;
    }

    public function addLogro(Logros $logro): static
    {
        if (!$this->logros->contains($logro)) {
            $this->logros->add($logro);
            $logro->setEmpleado($this);
        }

        return $this;
    }

    public function removeLogro(Logros $logro): static
    {
        if ($this->logros->removeElement($logro)) {
            // set the owning side to null (unless already changed)
            if ($logro->getEmpleado() === $this) {
                $logro->setEmpleado(null);
            }
        }

        return $this;
    }
}
