<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\TimestampableEntity;
use App\Entity\Traits\HasIntIdentifierTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity()
 * @UniqueEntity(fields={"photo"})
 */
class ProjectPhoto
{
    use HasIntIdentifierTrait;
    use TimestampableEntity;

    /**
     * @var Project
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="photos")
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
     * @return string
     * @ApiProperty(iri="http://schema.org/name")
     */
    public function getName(): string
    {
        return $this->photo->getAlternateName();
    }

    /**
     * @return string
     * @ApiProperty(iri="http://schema.org/url")
     */
    public function getUrl(): ?string
    {
        //TODO ensure content url here is set using an doctrine listener for on load
        return $this->photo->getContentUrl();
    }

    public function getPhoto(): Image
    {
        return $this->photo;
    }

    public function setPhoto(Image $image): void
    {
        $this->photo = $image;
    }

    public function getProject(): Project
    {
        return $this->project;
    }

    public function setProject(Project $project): void
    {
        $this->project = $project;
    }
}