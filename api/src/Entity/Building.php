<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ApiResource
 * @ORM\Entity
 */
class Building
{
    /**
     * @var null|int The entity Id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=50, unique=true)
     * @Assert\NotBlank
     * @Assert\Length(min="5", max="50")
     */
    public $name = '';

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank
     */
    public $description;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $isPublished = false;

    /**
     * @var \DateTimeImmutable|null
     *
     * @ORM\Column(type="date_immutable", nullable=true)
     */
    private $datePublished;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIsPublished(): bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): void
    {
        if ($this->getIsPublished() && false === $isPublished) {
            throw new \InvalidArgumentException('Cannot un-publish building');
        }
        if (false === $this->isPublished && true === $isPublished) {
            $this->datePublished = new \DateTimeImmutable();
        }
        $this->isPublished = $isPublished;
    }

    /**
     * @return string|null The date when the building was first published formatted ISO-8601 or null if not published
     */
    public function getDatePublished(): ?string
    {
        return null !== $this->datePublished ? $this->datePublished->format(\DateTimeImmutable::ISO8601) : null;
    }
}
