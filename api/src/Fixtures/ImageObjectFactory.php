<?php
declare(strict_types=1);

namespace App\Fixtures;

use App\Entity\Image;
use App\Entity\ProjectPhoto;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ImageObjectFactory
{
    const IMAGE_NAME_PREFIX = 'fixture';

    public static function createImage($suffix): Image
    {
        $imageName = self::imageName($suffix);
        $image = new Image();
        $image->setFile(self::createUploadedImage($imageName));
        $image->setAlternateName($imageName);
        return $image;
    }

    public static function createFixtureImageFile(string $suffix): UploadedFile
    {
        return self::createUploadedImage(self::imageName($suffix));
    }

    private static function createUploadedImage(string $originalName): UploadedFile
    {
        $faker = self::createFaker();
        $imagePath = $faker->image(null, 640, 480, 'city');
        $uploadedFile = new UploadedFile($imagePath, $originalName, null, 0, true);

        return $uploadedFile;
    }

    private static function imageName($suffix): string
    {
        return sprintf('%s-%s.jpg', self::IMAGE_NAME_PREFIX, $suffix);
    }

    private static function createFaker(): \Faker\Generator
    {
        $faker = Factory::create();

        return $faker;
    }

    public static function isFixture(\SplFileInfo $file): bool
    {
        return strpos($file->getFilename(), self::IMAGE_NAME_PREFIX) === 0;
    }
}