<?php

namespace App\Entity\Company;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
#[ApiResource(
    operations: [
        new get(normalizationContext: ['groups' => ['departmentOne:read']],security: "is_granted('ROLE_USER')"),
        new GetCollection(normalizationContext: ['groups' => ['department:read']],security: "is_granted('ROLE_USER')"),
    ],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 7
)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['department:read','vacationRequest:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['department:read','department:write','employee:read','departmentOne:read','vacationRequest:read'])]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'department', targetEntity: Employee::class)]
    #[Groups(['departmentOne:read'])]
    private Collection $employees;

    #[ORM\Column(nullable: true)]
    private ?int $bitrixId = null;

    public function __construct()
    {
        $this->employees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, Employee>
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee): static
    {
        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
            $employee->setDepartment($this);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): static
    {
        if ($this->employees->removeElement($employee)) {
            // set the owning side to null (unless already changed)
            if ($employee->getDepartment() === $this) {
                $employee->setDepartment(null);
            }
        }

        return $this;
    }

    public function getBitrixId(): ?int
    {
        return $this->bitrixId;
    }

    public function setBitrixId(?int $bitrixId): static
    {
        $this->bitrixId = $bitrixId;

        return $this;
    }
}
