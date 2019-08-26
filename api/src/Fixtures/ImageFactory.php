<?php
declare(strict_types=1);

namespace App\Fixtures;

use App\Entity\Image;
use Faker\Factory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class ImageFactory
{
    const IMAGE_NAME_PREFIX = 'fixture';

    /** @var null|callable Callable which returns an image file path when invoked */
    private static $imageFilePathFactory = null;

    public static function createImage($suffix): Image
    {
        $image = new Image();
        $uploadedFile = self::createUploadedImage($suffix);
        $image->setFile($uploadedFile);
        $image->setAlternateName($uploadedFile->getClientOriginalName());

        return $image;
    }

    public static function createUploadedFile(string $suffix): UploadedFile
    {
        return self::createUploadedImage($suffix);
    }

    private static function createUploadedImage(string $suffix): UploadedFile
    {
        $imagePath = self::getImageFactory()();
        $originalName = sprintf('%s.%s', self::imageName($suffix), pathinfo($imagePath, PATHINFO_EXTENSION));
        $uploadedFile = new UploadedFile($imagePath, $originalName, null, 0, true);

        return $uploadedFile;
    }

    private static function getImageFactory(): callable
    {
        if (null === self::$imageFilePathFactory) {
            self::setImageFilePathFactory(self::getDefaultImageFactory());
        }

        return self::$imageFilePathFactory;
    }

    /**
     * All file created by this factory must have the prefix 'fixture' so that they can be safely removed
     * after the tests are run
     */
    private static function imageName($suffix): string
    {
        return sprintf('%s-%s', self::IMAGE_NAME_PREFIX, $suffix);
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

    private static function getDefaultImageFactory(): callable
    {
        $faker = self::createFaker();

        return function () use ($faker) {
            return $faker->image(null, 640, 480, 'city');
        };
    }

    /**
     * This method allows using local images instead of requesting them from lorempixel using Faker library
     */
    public static function setImageFilePathFactory(callable $imageFilePathFactory): void
    {
        self::$imageFilePathFactory = $imageFilePathFactory;
    }

    public static function unsetImageFactory(): void
    {
        self::$imageFilePathFactory = null;
    }
}