<?php

namespace App\Entity\Vacation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\VacationTypesRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VacationTypesRepository::class)]
#[ApiResource(
    operations: [
        new get(normalizationContext: ['groups' => ['vacationType:read']],security: "is_granted('ROLE_USER')"),
        new GetCollection(normalizationContext: ['groups' => ['vacationType:read']],security: "is_granted('ROLE_USER')"),
    ],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 7
)]
class VacationTypes
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['vacationType:read'])]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['vacationType:read', 'vacationType:write','vacationRequest:read','vacationLimit:read','employee:read'])]
    public ?string $name = null;

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
}
