<?php

namespace App\Entity\Vacation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Company\Employee;
use App\Repository\EmployeeVacationLimitRepository;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EmployeeVacationLimitRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new get(normalizationContext: ['groups' => ['vacationLimit:read']],security: "is_granted('ROLE_USER')"),
        new GetCollection(normalizationContext: ['groups' => ['vacationLimit:read']],security: "is_granted('ROLE_USER')"),
        new Post(denormalizationContext: ['groups' => ['vacationLimit:write']],security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_MOD')"),
        new Put(denormalizationContext: ['groups' => ['vacationLimit:write']],security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_MOD')"),
        new Delete(denormalizationContext: ['groups' => ['vacationLimit:write']],security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_MOD')")
    ],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 7,
)]
class VacationLimits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vacationLimit:read', 'vacationLimit:write','employee:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'vacationLimits')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups([ 'vacationLimit:write'])]
    #[Assert\NotBlank]
    private ?Employee $Employee = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vacationLimit:read', 'vacationLimit:write','employee:read', 'vacationRequest:read'])]
    #[Assert\NotBlank]
    private ?VacationTypes $vacationType = null;

    #[ORM\Column]
    #[Groups(['vacationLimit:read', 'vacationLimit:write','employee:read','vacationRequest:read'])]
    #[Assert\GreaterThanOrEqual(0, message: "Limit nie może być niższy niż 0")]
    #[Assert\LessThanOrEqual(100, message: "Limit nie może być wyższy niż 100 dni")]
    private ?int $daysLimit = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['vacationLimit:read', 'vacationLimit:write','employee:read','vacationRequest:read'])]
    private ?int $unusedDaysFromPreviousYear = null;

    #[ORM\PrePersist]
    public function prePersist(PrePersistEventArgs $args):void
    {
        $entityManager = $args->getObjectManager();
        $vacationRepository = $entityManager->getRepository(VacationLimits::class);

        if($vacationRepository -> findTypeForEmployee($this->Employee,$this->vacationType) !== null)
        {
            throw new BadRequestException("Limit tego typu urlopu został już przypisany dla tego użytkownika.",403);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmployee(): ?Employee
    {
        return $this->Employee;
    }

    public function setEmployee(?Employee $Employee): static
    {
        $this->Employee = $Employee;

        return $this;
    }

    public function getVacationType(): ?VacationTypes
    {
        return $this->vacationType;
    }

    public function setVacationType(?VacationTypes $vacationType): static
    {
        $this->vacationType = $vacationType;

        return $this;
    }

    public function getDaysLimit(): ?int
    {
        return $this->daysLimit;
    }

    public function setDaysLimit(int $daysLimit): static
    {
        $this->daysLimit = $daysLimit;

        return $this;
    }

    public function getUnusedDaysFromPreviousYear(): ?int
    {
        return $this->unusedDaysFromPreviousYear;
    }

    public function setUnusedDaysFromPreviousYear(?int $unusedDaysFromPreviousYear): static
    {
        $this->unusedDaysFromPreviousYear = $unusedDaysFromPreviousYear;

        return $this;
    }
}
