<?php

namespace App\Entity;

use App\Repository\ApiTokenRepository;
use DateInterval;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ApiTokenRepository::class)]
class ApiToken
{
    private const PERSONAL_ACCESS_TOKEN = 'bup_';

    public const SCOPE_USER_EDIT = 'ROLE_USER_EDIT';
    public const SCOPE_USER_CREATE = 'ROLE_USER_CREATE';
    public const SCOPE_EMPLOYEE_EDIT = 'ROLE_EMPLOYEE_EDIT';

    public const SCOPES = [
        self::SCOPE_USER_EDIT => 'Edit User',
        self::SCOPE_USER_CREATE => 'Create User',
        self::SCOPE_EMPLOYEE_EDIT => 'Edit Employee'
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'apiTokens')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $ownedBy = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $expiresAt = null;

    #[ORM\Column(length: 255)]
    private string $token;

    #[ORM\Column]
    private array $scopes = [];

    /**
     * @throws \Exception
     */
    public function __construct(string $tokenType = self::PERSONAL_ACCESS_TOKEN)
    {
        $this->token = $tokenType . bin2hex(random_bytes(32));
        $this->expiresAt = new \DateTimeImmutable();
        $this->expiresAt =  $this->expiresAt->add(new DateInterval('P1D'));
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwnedBy(): ?User
    {
        return $this->ownedBy;
    }

    public function setOwnedBy(?User $ownedBy): static
    {
        $this->ownedBy = $ownedBy;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): static
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getScopes(): array
    {
        return $this->scopes;
    }

    public function setScopes(array $scopes): static
    {
        $this->scopes = $scopes;

        return $this;
    }

    public function isValid():bool
    {
        return $this->expiresAt === null || $this->expiresAt > new \DateTimeImmutable();
    }
}
