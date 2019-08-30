<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Image;
use App\Tests\Fixtures\ImageFactory;
use App\Tests\ApiFunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ImagesEndpointTest extends ApiFunctionalTestCase
{
    public const ENDPOINT_IMAGES = '/images';

    public function testImageCanBeUploadedUsingMultipartFormData()
    {
        $files = [
            'imageFile' => ImageFactory::createUploadedFile('testCreate'),
        ];
        $postFields = ['alternateName' => 'Alternate name for this test image'];
        $image = $this->postMultipartFormData($files, $postFields);
        self::assertResponseStatusCodeSame(201);

        self::assertNotNull($image['contentUrl']);
        self::assertEquals($postFields['alternateName'], $image['alternateName']);
        self::assertStringStartsWith('http://localhost/media/images', $image['contentUrl']);
    }

    public function testImageNameIsUsedAsAlternateNameByDefault()
    {
        $files = ['imageFile' => ImageFactory::createUploadedFile('test-alternate-default')];
        $image = $this->postMultipartFormData($files, []);
        self::assertResponseStatusCodeSame(201);
        self::assertEquals('fixture-test-alternate-default.png', $image['alternateName']);
    }

    public function testAlternateNameCanBeUpdatedUsingJson()
    {
        $imageIri = $this->findOneIriBy(Image::class, ['alternateName' => 'fixture-1.png']);
        $content = ['alternateName' => 'new name'];

        $response = $this->request('PUT', $imageIri, $content, []);
        self::assertResponseIsSuccessful();
        $updatedImage = $this->jsonDecode($response);
        self::assertEquals('new name', $updatedImage['alternateName']);
    }

    public function testImageCannotBeCreatedWithoutAFile()
    {
        $this->postMultipartFormData([], []);
        self::assertResponseStatusCodeSame(400);
    }

    public function testCanCreateImageByPostingBase64BinaryFile(): void
    {
        $base64EncodedImage = base64_encode(file_get_contents(__DIR__.'/../Fixtures/test-image.jpg'));

        $imageData = [
            'imageFile'     => "data:image/jpeg;base64,$base64EncodedImage",
            'alternateName' => 'fixture-image-uploaded-base64-encoded',
        ];
        $response = $this->request('POST', self::ENDPOINT_IMAGES, $imageData);
        self::assertResponseStatusCodeSame(201);
        $image = $this->jsonDecode($response);
        self::assertEquals('fixture-image-uploaded-base64-encoded', $image['alternateName']);
    }

    protected function postMultipartFormData(array $files, array $postFields): array
    {
        $headers = [
            self::CONTENT_TYPE => self::MULTIPART_FORM_DATA,
        ];
        $server = $this->createServer($headers);
        $this->getBrowser()->request('POST', self::ENDPOINT_IMAGES, $postFields, $files, $server);

        return $this->jsonDecode($this->getBrowser()->getResponse());
    }
}
