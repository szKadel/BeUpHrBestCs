<?php

namespace App\Entity\Vacation;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;

use App\Controller\Entity\CreatedVacationFile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[Vich\Uploadable]
#[ORM\Entity]
#[ApiResource(
    types: ['https://schema.org/MediaObject'],
    operations: [
        new Get(normalizationContext: ['groups' => ['vacation_file:read']]),
        new GetCollection(normalizationContext: ['groups' => ['vacation_file:read']]),
        new Post(
            controller: CreatedVacationFile::class,
            openapi: new Model\Operation(
                requestBody: new Model\RequestBody(
                    content: new \ArrayObject([
                        'multipart/form-data' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'file' => [
                                        'type' => 'string',
                                        'format' => 'binary'
                                    ]
                                ]
                            ]
                        ]
                    ])
                )
            ),
            validationContext: ['groups' => ['Default', 'vacation_file:create']],
            deserialize: false
        )
    ],
    normalizationContext: ['groups' => ['vacation_file:read']]
)]
class VacationFile
{
    #[ORM\Id, ORM\Column, ORM\GeneratedValue]
    #[Groups(['vacation_file:read','vacationRequest:read'])]
    private ?int $id = null;

    #[ApiProperty(types: ['https://schema.org/contentUrl'])]
    #[Groups(['vacation_file:read','vacationRequest:read', 'vacationRequest:write','vacationRequest:update'])]
    public ?string $contentUrl = null;

    #[Vich\UploadableField(mapping: "media_object", fileNameProperty: "filePath")]
    #[Assert\NotNull(groups: ['vacation_file:create'])]
    #[Groups(['vacation_file:read','vacationRequest:read', 'vacationRequest:write','vacationRequest:update'])]
    public ?File $file = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['vacation_file:read','vacationRequest:read', 'vacationRequest:write','vacationRequest:update'])]
    public ?string $filePath = null;

    #[Groups(['vacation_file:read','vacationRequest:read', 'vacationRequest:write','vacationRequest:update'])]
    public ?string $newFileName = null;

    #[ORM\OneToMany(mappedBy: 'file', targetEntity: Vacation::class)]
    private Collection $vacations;

    public function __construct()
    {
        $this->vacations = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getContentUrl(): ?string
    {
        return $this->contentUrl;
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
            $vacation->setFile($this);
        }

        return $this;
    }

    public function removeVacation(Vacation $vacation): static
    {
        if ($this->vacations->removeElement($vacation)) {
            // set the owning side to null (unless already changed)
            if ($vacation->getFile() === $this) {
                $vacation->setFile(null);
            }
        }

        return $this;
    }

    /**
     * @return string|null
     */
    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    /**
     * @return string|null
     */
    public function getNewFileName(): ?string
    {
        return $this->newFileName ?? null;
    }

    /**
     * @param string|null $newFileName
     */
    public function setNewFileName(?string $newFileName): void
    {
        $this->newFileName = $newFileName;
    }

    /**
     * @param File|null $file
     */
    public function setFile(?File $file): void
    {
        $this->file = $file;
        $this->setNewFileName($file?->getFilename());
    }

}