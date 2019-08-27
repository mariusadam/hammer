<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\HasIntIdentifierTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource(
 *     normalizationContext={"groups"={"person:read"}},
 *     itemOperations={
 *          "get"={},
 *          "delete"={},
 *          "put"={"denormalization_context"={"groups"={"person:update"}}}
 *     },
 *     collectionOperations={
 *          "get"={},
 *          "post"={"denormalization_context"={"groups"={"person:create"}}}
 *     }
 * )
 * @ORM\Entity()
 * @UniqueEntity(fields={"email"})
 */
class Person
{
    use HasIntIdentifierTrait;

    /**
     * @var string The name of the person
     *
     * @ORM\Column(type="string")
     * @Groups({"person:create", "person:read", "person:update"})
     * @Assert\NotNull()
     * @ApiProperty(iri="http://schema.org/name")
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(type="string", unique=true)
     * @Assert\Email()
     * @Assert\NotNull()
     * @Groups({"person:create", "person:read"})
     * @ApiProperty(iri="http://schema.org/email")
     */
    private $email;

    /**
     * @var Collection Projects that this person was/is in charge of
     * @ORM\OneToMany(targetEntity="Project", mappedBy="foreman", cascade={"persist"})
     * @Groups({"person:create", "person:read", "person:update"})
     */
    private $ledProjects;

    /**
     * @var null|Image
     * @ORM\ManyToOne(targetEntity="Image")
     * @ORM\JoinColumn(nullable=true)
     * @ApiProperty(iri="http://schema.org/ImageObject")
     * @Groups({"person:read", "person:create", "person:update"})
     */
    private $image;

    public function __construct()
    {
        $this->ledProjects = new ArrayCollection();
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): void
    {
        $this->image = $image;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLedProjects(): Collection
    {
        return $this->ledProjects;
    }

    public function removeLedProject(Project $project): void
    {
        if ($this->ledProjects->contains($project)) {
            $this->ledProjects->removeElement($project);
            $project->unAssignForeman($this);
        }
    }

    public function addLedProject(Project $project): void
    {
        if ($this->ledProjects->contains($project)) {
            return;
        }

        $this->ledProjects->add($project);
        $project->setForeman($this);
    }
}
