<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\Traits\TimestampableEntity;
use App\Entity\Traits\HasIntIdentifierTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ApiResource()
 * @ORM\HasLifecycleCallbacks()
 */
class Project
{
    use HasIntIdentifierTrait;
    use TimestampableEntity;

    /**
     * @var string Unique project name
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotNull()
     * @ApiProperty(iri="http://schema.org/name")
     */
    private $name;

    /**
     * @var Person The person in charge this project
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="ledProjects")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotNull()
     */
    private $foreman;

    /**
     * @var Collection
     * @ApiSubresource()
     * @ORM\OneToMany(targetEntity="ProjectPhoto", mappedBy="project", cascade={"remove"})
     */
    private $photos;

    /**
     * @var string
     * @ORM\Column(type="text")
     * @Assert\NotNull()
     * @Assert\NotBlank()
     * @Assert\Length(min="10", max="10000")
     */
    private $description;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function addPhoto(ProjectPhoto $photo): void
    {
        if ($this->photos->contains($photo)) {
            return;
        }

        $this->photos->add($photo);
        $photo->setProject($this);
    }

    public function removePhoto(ProjectPhoto $photo): void
    {
        if ($this->photos->contains($photo)) {
            $this->photos->removeElement($photo);
            $photo->unAssignProject($this);
        }
    }

    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setForeman(Person $foreman): void
    {
        $this->foreman = $foreman;
        $foreman->addLedProject($this);
    }

    public function getForeman(): ?Person
    {
        return $this->foreman;
    }

    public function unAssignForeman(Person $foreman): void
    {
        $this->foreman = null;
        $foreman->removeLedProject($this);
    }

    /**
     * @ORM\PreUpdate() Tells the ORM to invoke this method before doing the update
     *
     * This is need because when updating the foreman from the person resource by adding or removing led projects
     * we might end up without having a foreman set for the project.
     */
    public function assertForemanIsNotNull(): void
    {
        if (null === $this->getForeman()) {
            // TODO not the best way to handle this, it should be a custom exception, but good enough for now
            throw new BadRequestHttpException('Cannot save project without a foreman');
        }
    }
}
