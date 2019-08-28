<?php
declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Entity\Traits\HasIntIdentifierTrait;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\HttpFoundation\File\File;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\DateFilter;

/**
 * @ORM\Entity()
 * @UniqueEntity(fields={"alternateName"})
 * @ApiResource(
 *     iri="http://schema.org/ImageObject",
 *     collectionOperations={
 *          "get"={},
 *          "post"={
 *              "controller"="App\Controller\CreateImageObjectAction",
 *              "deserialize"=false,
 *              "swagger_context"={
 *                  "consumes"={
 *                      "multipart/form-data",
 *                  },
 *                  "parameters"={
 *                      {
 *                          "in"="formData",
 *                          "name"="imageFile",
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
 *          "put"={},
 *          "delete"={},
 *     }
 * )
 * @ApiFilter(DateFilter::class, properties={"uploadDate"})
 * @Vich\Uploadable()
 */
class Image
{
    use HasIntIdentifierTrait;

    /**
     * @var string|null
     *
     * @ApiProperty(iri="http://schema.org/contentUrl", writable=false)
     */
    private $contentUrl;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="create")
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
     * @ApiProperty(readable=false)
     */
    private $imageFile;

    /**
     * @var null|string
     *
     * @ORM\Column(nullable=false, unique=true)
     * @ApiProperty(writable=false, readable=false)
     */
    private $fileName;

    /**
     * @var string|null An alias for the image, defaults the to image file name
     *
     * @ORM\Column(type="string", unique=true)
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
        if ($file instanceof UploadedFile && null === $this->alternateName) {
            $this->setAlternateName($file->getClientOriginalName());
        }

        $this->imageFile = $file;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    public function setFileName(?string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getContentUrl(): string
    {
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
