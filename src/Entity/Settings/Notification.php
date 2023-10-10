<?php

namespace App\Entity\Settings;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\Settings\NotificationsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: NotificationsRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['notificationSetting:read']],security: "is_granted('ROLE_ADMIN')"),
        new Put(denormalizationContext: ['groups' => ['notificationSetting:update']],security: "is_granted('ROLE_ADMIN')")
    ],
    paginationClientItemsPerPage: true,
    paginationItemsPerPage: 7,
)]
class Notification
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['notificationSetting:read', 'notificationSetting:update'])]
    private ?bool $NotifcateAdminOnAcceptVacation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['notificationSetting:read', 'notificationSetting:update'])]
    private ?bool $NotificateDepartmentModOnCreatedVacation = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['notificationSetting:read', 'notificationSetting:update'])]
    private ?bool $NotificateReplacmentUser = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['notificationSetting:read', 'notificationSetting:update'])]
    private ?bool $NotificateUserOnVacationRequestAccept = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isNotifcateAdminOnAcceptVacation(): ?bool
    {
        return $this->NotifcateAdminOnAcceptVacation;
    }

    public function setNotifcateAdminOnAcceptVacation(?bool $NotifcateAdminOnAcceptVacation): static
    {
        $this->NotifcateAdminOnAcceptVacation = $NotifcateAdminOnAcceptVacation;

        return $this;
    }

    public function isNotificateDepartmentModOnCreatedVacation(): ?bool
    {
        return $this->NotificateDepartmentModOnCreatedVacation;
    }

    public function setNotificateDepartmentModOnCreatedVacation(?bool $NotificateDepartmentModOnCreatedVacation): static
    {
        $this->NotificateDepartmentModOnCreatedVacation = $NotificateDepartmentModOnCreatedVacation;

        return $this;
    }

    public function isNotificateReplacmentUser(): ?bool
    {
        return $this->NotificateReplacmentUser;
    }

    public function setNotificateReplacmentUser(?bool $NotificateReplacmentUser): static
    {
        $this->NotificateReplacmentUser = $NotificateReplacmentUser;

        return $this;
    }

    public function isNotificateUserOnVacationRequestAccept(): ?bool
    {
        return $this->NotificateUserOnVacationRequestAccept;
    }

    public function setNotificateUserOnVacationRequestAccept(?bool $NotificateUserOnVacationRequestAccept): static
    {
        $this->NotificateUserOnVacationRequestAccept = $NotificateUserOnVacationRequestAccept;

        return $this;
    }
}
