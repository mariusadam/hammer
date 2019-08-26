<?php
declare(strict_types=1);

namespace App\Controller;

use App\Entity\Image;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

final class CreateImageObjectAction
{
    private $denormalizer;

    public function __construct(DenormalizerInterface $denormalizer)
    {
        $this->denormalizer = $denormalizer;
    }

    public function __invoke(Request $request): Image
    {
        $uploadedFile = $request->files->get('file');
        if (!$uploadedFile instanceof UploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        $imagePostData = $request->request->all() + ['alternateName' => $uploadedFile->getClientOriginalName()];
        $image = $this->denormalizeImage($imagePostData);
        $image->setFile($uploadedFile);

        return $image;
    }

    private function denormalizeImage(array $imagePostData): Image
    {
        $image = $this->denormalizer->denormalize($imagePostData, Image::class);
        if ($image instanceof Image) {
            return $image;
        }

        throw new UnprocessableEntityHttpException('Cannot create image from given input');
    }
}