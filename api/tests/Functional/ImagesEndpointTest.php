<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Image;
use App\Fixtures\ImageFactory;
use App\Tests\ApiFunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ImagesEndpointTest extends ApiFunctionalTestCase
{
    public const ENDPOINT_IMAGE = '/images';

    public function testImageCanBeUploaded()
    {
        $files = [
            'file' => ImageFactory::createUploadedFile('testCreate'),
        ];
        $postFields = ['alternateName' => 'Alternate name for this test image'];
        $response = $this->sendCreateImageRequest($files, $postFields);
        self::assertResponseStatusCodeSame(201);

        $image = $this->jsonDecode($response);
        self::assertNotNull($image['contentUrl']);
        self::assertEquals($postFields['alternateName'], $image['alternateName']);
        self::assertStringStartsWith('http://localhost/media/images', $image['contentUrl']);
    }

    public function testImageNameIsUsedAsAlternateNameByDefault()
    {
        $files = ['file' => ImageFactory::createUploadedFile('test-alternate-default')];
        $response = $this->sendCreateImageRequest($files, []);
        self::assertResponseStatusCodeSame(201);
        $image = $this->jsonDecode($response);
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
        $this->sendCreateImageRequest([], []);
        self::assertResponseStatusCodeSame(400);
    }

    protected function sendCreateImageRequest(array $files, array $postFields): Response
    {
        $headers = [
            self::CONTENT_TYPE => self::MULTIPART_FORM_DATA,
        ];
        $server = $this->createServer($headers);
        $this->getBrowser()->request('POST', self::ENDPOINT_IMAGE, $postFields, $files, $server);

        return $this->getBrowser()->getResponse();
    }
}
