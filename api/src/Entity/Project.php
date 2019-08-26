<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use App\Entity\Traits\TimestampableEntity;
use App\Entity\Traits\HasIntIdentifierTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity()
 * @ApiResource()
 */
class Project
{
    use HasIntIdentifierTrait;
    use TimestampableEntity;

    /**
     * @var string Unique project name
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotNull()
     */
    private $name;

    /**
     * @var Person The person in charge this project
     * @ORM\ManyToOne(targetEntity="Person", inversedBy="ledProjects")
     * @Assert\NotNull()
     */
    private $foreman;

    /**
     * @var Collection
     * @ApiSubresource()
     * @ORM\OneToMany(targetEntity="ProjectPhoto", mappedBy="project")
     */
    private $photos;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
    }

    public function addPhoto(ProjectPhoto $photo): void
    {
        if ($this->photos->contains($photo)) {
            return;
        }

        $this->photos->add($photo);

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
    }

    public function getForeman(): Person
    {
        return $this->foreman;
    }
}
