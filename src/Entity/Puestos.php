<?php

namespace App\Entity;

use App\Repository\PuestosRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PuestosRepository::class)]
class Puestos
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $nombre = null;

    /**
     * @var Collection<int, Empleados>
     */
    #[ORM\OneToMany(targetEntity: Empleados::class, mappedBy: 'puesto')]
    private Collection $empleados;

    public function __construct()
    {
        $this->empleados = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;

        return $this;
    }

    /**
     * @return Collection<int, Empleados>
     */
    public function getEmpleados(): Collection
    {
        return $this->empleados;
    }

    public function addEmpleado(Empleados $empleado): static
    {
        if (!$this->empleados->contains($empleado)) {
            $this->empleados->add($empleado);
            $empleado->setPuesto($this);
        }

        return $this;
    }

    public function removeEmpleado(Empleados $empleado): static
    {
        if ($this->empleados->removeElement($empleado)) {
            // set the owning side to null (unless already changed)
            if ($empleado->getPuesto() === $this) {
                $empleado->setPuesto(null);
            }
        }

        return $this;
    }
}
