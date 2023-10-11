<?php

namespace App\Entity\Vacation;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Company\Employee;
use App\Repository\VacationRepository;
use App\Service\WorkingDaysCounterService;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Serializer\Annotation\Context;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: VacationRepository::class)]
#[ORM\HasLifecycleCallbacks]

#[ApiResource(
    operations: [
        new get(normalizationContext: ['groups' => ['vacationRequest:read']],security: "is_granted('ROLE_USER')"),
        new GetCollection(normalizationContext: ['groups' => ['vacationRequest:read']],security: "is_granted('ROLE_USER')"),
        new Post(denormalizationContext: ['groups' => ['vacationRequest:write']],security: "is_granted('ROLE_USER')"),
        new Put(denormalizationContext: ['groups' => ['vacationRequest:update']],security: "is_granted('ROLE_USER')"),
        new Delete(security: "is_granted('ROLE_USER')")
    ],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 7,
)]
#[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class,properties: ['employee.department'=>'exact'])]
class Vacation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups('vacationRequest:read')]
    private ?int $id = null;


    #[ORM\ManyToOne(inversedBy: 'vacations')]

    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank]
    #[Groups(['vacationRequest:read', 'vacationRequest:write'])]
    #[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class,strategy: 'exact')]
    private ?Employee $employee = null;

    #[ORM\ManyToOne]
    #[Assert\NotBlank]
    #[ORM\JoinColumn(nullable: false)]
    #[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class,strategy: 'exact')]
    #[Groups(['vacationRequest:read', 'vacationRequest:write','vacationRequest:update'])]
    private ?VacationTypes $type = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[ApiFilter(DateFilter::class)]
    #[Assert\NotBlank]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[Groups(['vacationRequest:read', 'vacationRequest:write','vacationRequest:update'])]
    private ?\DateTimeInterface $dateFrom = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank]
    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[ApiFilter(DateFilter::class)]
    #[Groups(['vacationRequest:read', 'vacationRequest:write','vacationRequest:update'])]
    private ?\DateTimeInterface $dateTo = null;

    #[ORM\Column]
    #[Groups('vacationRequest:read')]
    private ?int $SpendVacationDays = null;

    #[ORM\ManyToOne]
    #[Groups(['vacationRequest:read', 'vacationRequest:write','vacationRequest:update'])]
    private ?Employee $replacement = null;

    #[ORM\ManyToOne]
    #[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class,strategy: 'exact')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['vacationRequest:read','vacationRequest:update'])]
    private ?VacationStatus $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['vacationRequest:read', 'vacationRequest:write','vacationRequest:update'])]
    private ?string $comment = null;

    #[ORM\Column(type: "datetime",nullable: true)]
    #[Groups(['vacationRequest:read'])]
    private $createdAt = null;

    #[ORM\Column(type: "datetime",nullable: true)]
    #[Groups(['vacationRequest:read'])]
    private $updatedAt = null;

    #[ORM\Column(type: "datetime",nullable: true)]
    #[Groups(['vacationRequest:read'])]
    private $acceptedAt = null;

    #[ORM\Column(type: "datetime",nullable: true)]
    #[Groups(['vacationRequest:read'])]
    private $anulatedAt = null;


    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
    }

    #[ORM\PreUpdate]
    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        if($this->type?->getId() == 1 || $this->type->getId() == 11) {
            throw new BadRequestException("Nie można zaakceptować wniosku o tym typie. Określ typ wniosku.",403);
        }

        if($this->type->getName() == "Inny" && $eventArgs->getNewValue("status")->getName() == "Zaakceptowany")
        {
            throw new BadRequestException("Nie można zaakceptować ani odrzucić wniosku o typie urlopu inny, zmień typ i spróbuj ponownie.",403);
        }

        if($this->status?->getName() == "Anulowany")
        {
            $this->setAnulatedAt(new \DateTime());;
        }

        if($this->status?->getName() == "Zaakceptowany")
        {
            $this->setAcceptedAt(new \DateTime());
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmployee(): ?Employee
    {
        return $this->employee;
    }

    public function setEmployee(?Employee $employee): static
    {
        $this->employee = $employee;

        return $this;
    }

    public function getType(): ?VacationTypes
    {
        return $this->type;
    }

    public function setType(?VacationTypes $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getDateFrom(): ?\DateTimeInterface
    {
        return $this->dateFrom;
    }

    public function setDateFrom(\DateTimeInterface $dateFrom): static
    {
        $this->dateFrom = $dateFrom;

        return $this;
    }

    public function getDateTo(): ?\DateTimeInterface
    {
        return $this->dateTo;
    }

    public function setDateTo(\DateTimeInterface $dateTo): static
    {
        $this->dateTo = $dateTo;
        $this->setSpendVacationDays();
        return $this;
    }

    public function getSpendVacationDays(): ?int
    {
        return $this->SpendVacationDays;
    }

    private function setSpendVacationDays(): static
    {
        $this->SpendVacationDays = WorkingDaysCounterService::countWorkingDays($this->dateFrom,$this->dateTo);

        return $this;
    }

    public function getReplacement(): ?Employee
    {
        return $this->replacement;
    }

    public function setReplacement(?Employee $replacement): static
    {
        $this->replacement = $replacement;

        return $this;
    }

    public function getStatus(): ?VacationStatus
    {
        return $this->status;
    }

    public function setStatus(?VacationStatus $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): static
    {
        $this->comment = $comment;


        return $this;
    }

    /**
     * @return mixed
     */
    public function getAnulatedAt()
    {
        return $this->anulatedAt;
    }

    /**
     * @param mixed $anulatedAt
     */
    public function setAnulatedAt($anulatedAt): void
    {
        $this->anulatedAt = $anulatedAt;
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param mixed $createdAt
     */
    public function setCreatedAt($createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param mixed $updatedAt
     */
    public function setUpdatedAt($updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return mixed
     */
    public function getAcceptedAt()
    {
        return $this->acceptedAt;
    }

    /**
     * @param mixed $acceptedAt
     */
    public function setAcceptedAt($acceptedAt): void
    {
        $this->acceptedAt = $acceptedAt;
    }
}
