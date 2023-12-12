<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Company\Employee;
use App\Entity\Vacation\Vacation;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new get(normalizationContext: ['groups' => ['user:read']],security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_MOD') or is_granted('ROLE_KADR')"),
        new GetCollection(normalizationContext: ['groups' => ['user:read']],security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_MOD') or is_granted('ROLE_KADR')"),
        new Post(denormalizationContext: ['groups' => ['user:write']],security: "is_granted('ROLE_ADMIN')"),
        new Put(denormalizationContext: ['groups' => ['user:write']],security: "is_granted('ROLE_ADMIN') or is_granted('ROLE_KADR')"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 7
)]
#[UniqueEntity(fields: ['email'],message: 'This email has been allready register.')]
#[UniqueEntity(fields: ['username'],message: 'This username has been allready register.')]
#[ApiFilter(SearchFilter::class,properties: ['employee'=>'exact'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read', 'user:write','employee:read'])]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:write','employee:read'])]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column]
    #[Groups(['user:read','user:write'])]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255,unique: true)]
    #[Groups(['user:read', 'user:write','employee:read'])]
    #[Assert\NotBlank]
    private ?string $username = null;

    #[ORM\OneToMany(mappedBy: 'ownedBy', targetEntity: ApiToken::class)]
    private Collection $apiTokens;

    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    #[ApiFilter(\ApiPlatform\Doctrine\Orm\Filter\SearchFilter::class,strategy: 'exact')]
    #[Groups(['user:read', 'user:write','vacationRequest:read'])]
    private ?Employee $employee = null;

    #[Groups(['user:write'])]
    #[SerializedName('password')]
    private ?string $plainPassword = null;

    #[ORM\OneToMany(mappedBy: 'acceptedBy', targetEntity: Vacation::class)]
    private ?Collection $AcceptedVacations;

    public function __construct()
    {
        $this->apiTokens = new ArrayCollection();
        $this->AcceptedVacations = new ArrayCollection();
    }

    public function preRemove()
    {
        if(!empty($this->employee))
        {
            throw new BadRequestException("Nie można usunąć użytkownika który ma przypisanego pracownika.",400);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        //$this->password = password_hash($password, PASSWORD_BCRYPT); // You can use any hashing algorithm you prefer
        $this->password = $password;
        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @return Collection<int, ApiToken>
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): static
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens->add($apiToken);
            $apiToken->setOwnedBy($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): static
    {
        if ($this->apiTokens->removeElement($apiToken)) {
            if ($apiToken->getOwnedBy() === $this) {
                $apiToken->setOwnedBy(null);
            }
        }

        return $this;
    }

    public function getValidApiToken():array
    {
        return $this->getApiTokens()
            ->filter(fn(ApiToken $token) =>$token->isValid())
            ->map(fn(ApiToken $token)=>$token->getToken())
            ->toArray();
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

    public function setPlainPassword(?string $plainPassword):User
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getPlainPassword():?string
    {
        return $this->plainPassword??null;
    }



    /**
     * @return Collection<int, Vacation>
     */
    public function getAcceptedVacations(): Collection
    {
        return $this->AcceptedVacations;
    }

    public function addAcceptedVacation(Vacation $acceptedVacation): static
    {
        if (!$this->AcceptedVacations->contains($acceptedVacation)) {
            $this->AcceptedVacations->add($acceptedVacation);
            $acceptedVacation->setAcceptedBy($this);
        }

        return $this;
    }

    public function removeAcceptedVacation(Vacation $acceptedVacation): static
    {
        if ($this->AcceptedVacations->removeElement($acceptedVacation)) {
            // set the owning side to null (unless already changed)
            if ($acceptedVacation->getAcceptedBy() === $this) {
                $acceptedVacation->setAcceptedBy(null);
            }
        }

        return $this;
    }
}
