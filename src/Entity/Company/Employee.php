<?php

namespace App\Entity\Company;

use ApiPlatform\Doctrine\Odm\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Company;
use App\Entity\EmployeeExtendedAccesses;
use App\Entity\User;
use App\Entity\Vacation\VacationLimits;
use App\Entity\Vacation\Vacation;
use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[ApiResource(
    operations: [
        new get(normalizationContext: ['groups' => ['employee:read','employeeExtended:read']],security: "is_granted('ROLE_USER')"),
        new GetCollection(normalizationContext: ['groups' => ['employee:read','employeeExtended:read']],security: "is_granted('ROLE_USER')"),
        new Post(normalizationContext: ['groups' => ['employee:write']],security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_MOD')"),
        new Put(normalizationContext: ['groups' => ['employee:write']],security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_MOD')")
    ],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 7
)]
#[ApiFilter(OrderFilter::class, properties: ['unActive','name','surname','department','company'])]
class Employee
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['employee:read','vacationLimit:read','user:read'])]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class,strategy: 'partial')]
    #[Groups(['employee:read','employee:write','vacationRequest:read','vacationLimit:read','user:read','user:write','user:read','departmentOne:read'])]
    public ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\NotBlank]
    #[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class,strategy: 'partial')]
    #[Groups(['employee:read','employee:write','vacationRequest:read','vacationLimit:read','user:read','user:write','user:read','departmentOne:read'])]
    public ?string $surname = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['employee:read'])]
    private ?bool $isAdmin = null;

    #[ORM\ManyToOne(inversedBy: 'employees')]
    #[Assert\NotBlank]
    #[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class,strategy: 'exact')]
    #[Groups(['employee:read','employee:write','vacationLimit:read','user:read','user:write','user:read','vacationRequest:read'])]
    public ?Department $department = null;

    #[ORM\OneToMany(mappedBy: 'Employee', targetEntity: VacationLimits::class, orphanRemoval: true)]
    #[Groups(['employee:read','vacationLimit:read','vacationRequest:read'])]
    private Collection $vacationLimits;

    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: Vacation::class)]
    private Collection $vacations;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['employee:read'])]
    private ?string $email = null;

    #[ORM\Column(nullable: true)]
    private ?int $bitrixId = null;

    #[ORM\OneToOne(mappedBy: 'employee', cascade: ['persist', 'remove'])]
    #[Groups(['user:read','user:write','employee:read','employee:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'employees')]
    #[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class,strategy: 'exact')]
    #[Groups(['user:read','user:write','employee:read','employee:write'])]
    private ?Company $company = null;

    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: EmployeeExtendedAccesses::class, orphanRemoval: true)]
    #[Groups(['user:read','user:write','employeeExtended:read'])]
    private Collection $employeeExtendedAccesses;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'subordinates')]
    #[Groups(['employee:read','employee:write'])]
    private ?self $supervisor = null;

    #[ORM\OneToMany(mappedBy: 'supervisor', targetEntity: self::class)]
    private Collection $subordinates;

    #[ORM\Column()]
    #[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class,strategy: 'exact')]
    #[Groups(['employee:read','employee:write','vacationRequest:read'])]
    private ?bool $unActive = null;

    public function __construct()
    {
        $this->vacationLimits = new ArrayCollection();
        $this->vacations = new ArrayCollection();
        $this->employeeExtendedAccesses = new ArrayCollection();
        $this->subordinates = new ArrayCollection();
        $this->setUnActive(false);
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

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(?string $surname): static
    {
        $this->surname = $surname;

        return $this;
    }

    public function getDepartment(): ?Department
    {
        return $this->department;
    }

    public function setDepartment(?Department $department): static
    {
        $this->department = $department;

        return $this;
    }


    /**
     * @return Collection<int, VacationLimits>
     */
    public function getVacationLimits(): Collection
    {
        return $this->vacationLimits;
    }

    public function addType(VacationLimits $vacationLimit): static
    {
        if (!$this->vacationLimits->contains($vacationLimit)) {
            $this->vacationLimits->add($vacationLimit);
            $vacationLimit->setEmployee($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Vacation>
     */
    public function getVacations(): Collection
    {
        return $this->vacations;
    }

    public function addVacation(Vacation $vacation): static
    {
        if (!$this->vacations->contains($vacation)) {
            $this->vacations->add($vacation);
            $vacation->setEmployee($this);
        }

        return $this;
    }

    public function removeVacation(Vacation $vacation): static
    {
        if ($this->vacations->removeElement($vacation)) {
            // set the owning side to null (unless already changed)
            if ($vacation->getEmployee() === $this) {
                $vacation->setEmployee(null);
            }
        }

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsModerator(): ?bool
    {
        return $this->isModerator;
    }

    /**
     * @param bool|null $isModerator
     */
    public function setIsModerator(?bool $isModerator): void
    {
        $this->isModerator = $isModerator;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setEmployee(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getEmployee() !== $this) {
            $user->setEmployee($this);
        }

        $this->user = $user;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company;

        return $this;
    }

    /**
     * @return Collection<int, EmployeeExtendedAccesses>
     */
    public function getEmployeeExtendedAccesses(): Collection
    {
        return $this->employeeExtendedAccesses;
    }

    public function addEmployeeExtendedAccess(EmployeeExtendedAccesses $employeeExtendedAccess): static
    {
        if (!$this->employeeExtendedAccesses->contains($employeeExtendedAccess)) {
            $this->employeeExtendedAccesses->add($employeeExtendedAccess);
            $employeeExtendedAccess->setEmployee($this);
        }

        return $this;
    }

    public function removeEmployeeExtendedAccess(EmployeeExtendedAccesses $employeeExtendedAccess): static
    {
        if ($this->employeeExtendedAccesses->removeElement($employeeExtendedAccess)) {
            // set the owning side to null (unless already changed)
            if ($employeeExtendedAccess->getEmployee() === $this) {
                $employeeExtendedAccess->setEmployee(null);
            }
        }

        return $this;
    }

    public function getSupervisor(): ?self
    {
        return $this->supervisor;
    }

    public function setSupervisor(?self $supervisor): static
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSubordinates(): Collection
    {
        return $this->subordinates;
    }

    public function addSubordinate(self $subordinate): static
    {
        if (!$this->subordinates->contains($subordinate)) {
            $this->subordinates->add($subordinate);
            $subordinate->setSupervisor($this);
        }

        return $this;
    }

    public function removeSubordinate(self $subordinate): static
    {
        if ($this->subordinates->removeElement($subordinate)) {
            // set the owning side to null (unless already changed)
            if ($subordinate->getSupervisor() === $this) {
                $subordinate->setSupervisor(null);
            }
        }

        return $this;
    }

    public function getUnActive(): ?bool
    {
        return $this->unActive;
    }

    public function setUnActive(?bool $unActive): static
    {
        $this->unActive = $unActive;

        return $this;
    }
}
