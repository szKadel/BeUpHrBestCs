<?php

namespace App\Entity\Vacation;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Repository\VacationStatusRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: VacationStatusRepository::class)]
#[ApiResource(
    operations: [
        new get(normalizationContext: ['groups' => ['requestStatus:read']],security: "is_granted('ROLE_USER')"),
        new GetCollection(normalizationContext: ['groups' => ['requestStatus:read']],security: "is_granted('ROLE_USER')"),
    ],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 7
)]
class VacationStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['requestStatus:read','requestStatus:read'])]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['requestStatus:read', 'requestStatus:write','vacationRequest:read'])]
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
