<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\HasIntIdentifierTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity()
 * @UniqueEntity(fields={"alternateName"}, groups={"image:create", "image:update"})
 * @ApiResource(
 *     iri="http://schema.org/ImageObject",
 *     normalizationContext={"groups"={"image:read"}},
 *     collectionOperations={
 *          "get"={},
 *          "post"={
 *              "controller"="App\Controller\CreateImageObjectAction",
 *              "deserialize"=false,
 *              "validation_groups"={"image:create"},
 *              "denormalization_context"={"groups"={"image:create"}},
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
 *              "validation_groups"={"image:update"},
 *              "denormalization_context"={"groups"={"image:update"}}
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
     * @Groups({"image:read"})
     * @ApiProperty(iri="http://schema.org/url", writable=false)
     */
    private $contentUrl;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
     * @Groups({"image:read"})
     * @ApiProperty(writable=false)
     */
    private $uploadDate;

    /**
     * @var null|File
     *
     * This is not a field mapped to the db, it is just a regular property used to store the uploaded file
     * when the image resource is created for the first time
     *
     * @Vich\UploadableField(mapping="image", fileNameProperty="fileName")
     * @ApiProperty()
     * @Groups({"image:create"})
     * @Assert\NotNull()
     */
    private $imageFile;

    /**
     * @var File
     */
    public $tmpImageFile;

    /**
     * @var string
     *
     * @ORM\Column(nullable=false, unique=true)
     */
    private $fileName;

    /**
     * @var string|null An alias for the image, defaults the to image file name
     *
     * @ORM\Column(type="string", unique=true)
     * @Groups({"image:read", "image:create", "image:update"})
     * @ApiProperty(iri="http://schema.org/name")
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

    public function setImageFile(File $file): void
    {
        $this->imageFile = $file;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getContentUrl(): string
    {
        return $this->contentUrl ?? '';
        if (null === $this->contentUrl) {
            throw new \RuntimeException(
                'Field "contentUrl" is not stored in the database. It\'s value should have been populated right after the image was loaded from db'
            );
        }

        return $this->contentUrl;
    }

    public function setContentUrl(string $contentUrl): void
    {
        $this->contentUrl = $contentUrl;
    }
}
