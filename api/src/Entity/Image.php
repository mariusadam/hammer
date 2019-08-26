<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\HasIntIdentifierTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @ApiResource(
 *     iri="http://schema.org/ImageObject",
 *     normalizationContext={"groups"={"image-read"}},
 *     collectionOperations={
 *          "get"={},
 *          "post"={
 *              "controller"="App\Controller\CreateImageObjectAction",
 *              "deserialize"=false,
 *              "validation_groups"={"image-create"},
 *              "denormalization_context"={"groups"={"image-create"}},
 *              "swagger_context"={
 *                  "consumes"={
 *                      "multipart/form-data",
 *                  },
 *                  "parameters"={
 *                      {
 *                          "in"="formData",
 *                          "name"="file",
 *                          "type"="file",
 *                          "description"="The file to upload",
 *                      },
 *                      {
 *                          "in"="formData",
 *                          "name"="alternateName",
 *                          "type"="string",
 *                          "description"="An alias for the image",
 *                      },
 *                 },
 *             },
 *          }
 *     },
 *     itemOperations={
 *          "get"={},
 *          "put"={
 *              "validation_groups"={"image-update"},
 *              "denormalization_context"={"groups"={"image-update"}}
 *          }
 *     }
 * )
 * @Vich\Uploadable()
 */
class Image
{
    use HasIntIdentifierTrait;

    /**
     * @var string|null
     *
     * @Groups({"image-read"})
     */
    private $contentUrl;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Groups({"image-read"})
     * @ApiProperty(writable=false)
     */
    private $uploadDate;

    /**
     * @var File|null
     *
     * @Vich\UploadableField(mapping="image", fileNameProperty="filePath")
     * @ApiProperty()
     * @Groups({"image-create"})
     * @Assert\NotNull()
     */
    private $file;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     */
    private $filePath;

    /**
     * @var string|null An alias for the image
     * @ORM\Column(type="string")
     * @Groups({"image-read", "image-create", "image-update"})
     * @Assert\NotNull()
     */
    private $alternateName;

    public function getUploadDate(): \DateTime
    {
        return $this->uploadDate;
    }

    public function setUploadDate(\DateTime $uploadDate): void
    {
        $this->uploadDate = $uploadDate;
    }

    public function getAlternateName(): string
    {
        return $this->alternateName;
    }

    public function setAlternateName(string $alternateName): void
    {
        $this->alternateName = $alternateName;
    }

    public function setFile(File $uploadedFile): void
    {
        $this->file = $uploadedFile;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): void
    {
        $this->filePath = $filePath;
    }

    public function getContentUrl(): ?string
    {
        return $this->contentUrl;
    }

    public function updateContentUrl(string $contentUrl): void
    {
        $this->contentUrl = $contentUrl;
    }
}