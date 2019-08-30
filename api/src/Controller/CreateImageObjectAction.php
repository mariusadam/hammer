<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Image;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class CreateImageObjectAction
{
    private const FIELD_IMAGE_FILE = 'imageFile';
    private $denormalizer;
    private $decoder;

    public function __construct(DenormalizerInterface $denormalizer, DecoderInterface $decoder)
    {
        $this->decoder = $decoder;
        $this->denormalizer = $denormalizer;
    }

    public function __invoke(Request $request): Image
    {
        $contentType = $request->headers->get('Content-Type');
        if ('application/ld+json' === $contentType) {
            return $this->handleApplicationJsonRequest($request);
        }

        return $this->handleMultipartFormDataRequest($request);
    }

    private function handleApplicationJsonRequest(Request $request): Image
    {
        [$imageFile, $requestFields] = $this->extractImageFile($this->getDecodedBody($request));
        $image = $this->denormalizeImage($requestFields);
        $image->setImageFile($this->createUploadedFile($imageFile));

        return $image;
    }

    private function createUploadedFile(string $base64EncodedFile): UploadedFile
    {
        // here we fake a file upload in order to leverage vich uploader bundle which checks for UploadedFile instances
        [$prefix, $base64EncodedImage] = $this->extractImagePrefixAndContents($base64EncodedFile);
        $extension = $this->getExtensionFromBase64Prefix($prefix);
        $temporaryFilename = $this->createTemporaryImageFile();
        $this->saveImageTo($temporaryFilename, $base64EncodedImage);
        $originalName = $this->getOriginalName($temporaryFilename, $extension);

        return new UploadedFile($temporaryFilename, $originalName, null, null, true);
    }

    private function getOriginalName(string $temporaryFilename, string $extension): string
    {
        return sprintf('%s.%s', pathinfo($temporaryFilename, PATHINFO_BASENAME), $extension);
    }

    private function extractImagePrefixAndContents(string $base64EncodedFile): array
    {
        $parts = explode(',', $base64EncodedFile);
        if (count($parts) !== 2) {
            throw $this->createUnsupportedImageFormatException();
        }

        return $parts;
    }

    private function handleMultipartFormDataRequest(Request $request): Image
    {
        $uploadedFile = $request->files->get(self::FIELD_IMAGE_FILE);
        if (!$uploadedFile instanceof UploadedFile) {
            throw $this->createMissingFileException();
        }

        $image = $this->denormalizeImage($request->request->all());
        $image->setImageFile($uploadedFile);

        return $image;
    }

    private function extractImageFile(array $imageFields): array
    {
        if (!isset($imageFields[self::FIELD_IMAGE_FILE])) {
            throw $this->createMissingFileException();
        }
        $imageFile = $imageFields[self::FIELD_IMAGE_FILE];
        unset($imageFields[self::FIELD_IMAGE_FILE]);

        return [$imageFile, $imageFields];
    }

    private function getExtensionFromBase64Prefix(string $prefix): string
    {
        switch ($prefix) {
            case 'data:image/jpeg;base64':
                return 'jpg';
            case 'data:image/png;base64':
                return 'png';
            default:
                throw $this->createUnsupportedImageFormatException();
        }
    }

    private function denormalizeImage(array $imageFields): Image
    {
        $image = $this->denormalizer->denormalize($imageFields, Image::class);
        if ($image instanceof Image) {
            return $image;
        }

        throw new UnprocessableEntityHttpException('Cannot create image from given input');
    }

    private function getDecodedBody(Request $request): array
    {
        $options = [JsonDecode::ASSOCIATIVE => true];

        return $this->decoder->decode($request->getContent(), JsonEncoder::FORMAT, $options);
    }

    private function createUnsupportedImageFormatException(): \Exception
    {
        return new UnsupportedMediaTypeHttpException('Image format is not supported');
    }

    private function createMissingFileException(): \Exception
    {
        return new BadRequestHttpException(sprintf('"%s" is required', self::FIELD_IMAGE_FILE));
    }

    private function createTemporaryImageFile(): string
    {
        return tempnam(sys_get_temp_dir(), 'image-file-');
    }

    private function saveImageTo(string $temporaryFilename, string $base64EncodedImage): void
    {
        file_put_contents($temporaryFilename, base64_decode($base64EncodedImage));
    }
}
