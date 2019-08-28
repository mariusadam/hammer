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

    private function createMissingFileException(): \Exception
    {
        return new BadRequestHttpException('"imageFile" is required');
    }

    private function handleMultipartFormDataRequest(Request $request): Image
    {
        $uploadedFile = $request->files->get('imageFile');
        if (!$uploadedFile instanceof UploadedFile) {
            throw $this->createMissingFileException();
        }

        $image = $this->denormalizeImage($request->request->all());
        $image->setImageFile($uploadedFile);

        return $image;
    }

    private function handleApplicationJsonRequest(Request $request): Image
    {
        $imageFields = $this->getDecodedBody($request);
        if (!isset($imageFields['imageFile'])) {
            throw $this->createMissingFileException();
        }

        // here we fake a file upload in order to leverage vich uploader bundle which checks for UploadedFile instances
        $temporaryFilename = tempnam(sys_get_temp_dir(), 'image-file-');
        $parts = explode(',', $imageFields['imageFile']);
        if (count($parts) !== 2) {
            throw new UnsupportedMediaTypeHttpException('Image format is not supported');
        }
        [$prefix, $base64EncodedContents] = $parts;
        $extension = $this->getExtensionFromBase64Prefix($prefix);
        $originalName = pathinfo($temporaryFilename, PATHINFO_BASENAME);
        file_put_contents($temporaryFilename, base64_decode($base64EncodedContents));
        unset($imageFields['imageFile']);

        $originalName = sprintf('%s.%s', $originalName, $extension);
        $uploadedFile = new UploadedFile($temporaryFilename, $originalName, null, null, true);

        $image = $this->denormalizeImage($imageFields);
        $image->setImageFile($uploadedFile);

        return $image;
    }

    private function getExtensionFromBase64Prefix(string $prefix): string
    {
        switch ($prefix) {
            case 'data:image/jpeg;base64':
                return 'jpg';
            case 'data:image/png;base64':
                return 'png';
            default:
                throw new UnsupportedMediaTypeHttpException('Image format is not supported');
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
}
