<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\TimestampableEntity;
use App\Entity\Traits\HasIntIdentifierTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity()
 * @UniqueEntity(fields={"photo"})
 * @ORM\HasLifecycleCallbacks()
 */
class ProjectPhoto
{
    use HasIntIdentifierTrait;
    use TimestampableEntity;

    /**
     * @var Project
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="photos")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     */
    private $project;

    /**
     * @var Image
     * @ORM\OneToOne(targetEntity="Image")
     * @Assert\NotNull()
     */
    private $photo;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Length(min="10", max="255")
     */
    private $shortDescription;

    /**
     * @return string
     * @ApiProperty(iri="http://schema.org/name")
     */
    public function getName(): string
    {
        return sprintf('%s (project photo)', $this->photo->getAlternateName());
    }

    /**
     * @return string
     * @ApiProperty(iri="http://schema.org/url")
     */
    public function getUrl(): ?string
    {
        return $this->photo->getContentUrl();
    }

    public function getShortDescription(): string
    {
        return $this->shortDescription;
    }

    public function setShortDescription(string $shortDescription): void
    {
        $this->shortDescription = $shortDescription;
    }

    public function getPhoto(): Image
    {
        return $this->photo;
    }

    public function setPhoto(Image $photos): void
    {
        $this->photo = $photos;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
        $project->addPhoto($this);
    }

    public function unAssignProject(Project $project): void
    {
        $this->project = null;
        $project->removePhoto($this);
    }

    /** @ORM\PreUpdate() */
    public function assertProjectIsAssignedToAProject(): void
    {
        if (null === $this->project) {
            throw new BadRequestHttpException('Cannot save project photos without being assigned to a project');
        }
    }
}
