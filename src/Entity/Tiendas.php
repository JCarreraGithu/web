<?php

namespace App\Entity;

use App\Repository\TiendasRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TiendasRepository::class)]
class Tiendas
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * @var Collection<int, Empleados>
     */
    #[ORM\OneToMany(targetEntity: Empleados::class, mappedBy: 'tienda', orphanRemoval: true)]
    private Collection $empleados;

    #[ORM\Column(length: 50)]
    private ?string $nombre = null;

    public function __construct()
    {
        $this->empleados = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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
            $empleado->setTienda($this);
        }

        return $this;
    }

    public function removeEmpleado(Empleados $empleado): static
    {
        if ($this->empleados->removeElement($empleado)) {
            // set the owning side to null (unless already changed)
            if ($empleado->getTienda() === $this) {
                $empleado->setTienda(null);
            }
        }

        return $this;
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
}
