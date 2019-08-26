<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\TimestampableEntity;
use App\Entity\Traits\HasIntIdentifierTrait;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource()
 * @ORM\Entity()
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