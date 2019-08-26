<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Image;
use App\Entity\Project;
use App\Tests\ApiFunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ProjectPhotosEndpointTest extends ApiFunctionalTestCase
{
    public const ENDPOINT_PROJECT_PHOTOS = '/project_photos';

    public function testCannotCreateProjectPhotoWithoutProject()
    {
        $photoData = [
            'photo' => $this->findOneIriBy(Image::class, []),
        ];

        $response = $this->sendCreatePhotoRequest($photoData);
        self::assertResponseStatusCodeSame(400);
        self::assertEquals(
            'project: This value should not be null.',
            $this->hydraDescription($this->jsonDecode($response))
        );
    }

    public function testCannotCreateProjectPhotoWithoutImage()
    {
        $photoData = [
            'project' => $this->findOneIriBy(Project::class, []),
        ];

        $response = $this->sendCreatePhotoRequest($photoData);
        self::assertResponseStatusCodeSame(400);
        self::assertEquals(
            'photo: This value should not be null.',
            $this->hydraDescription($this->jsonDecode($response))
        );
    }

    public function ttestCanCreateProjectPhoto()
    {
        $photoData = [
            'photo'   => $this->findOneIriBy(Image::class, []),
            'project' => $this->findOneIriBy(Project::class, []),
        ];

        $response = $this->sendCreatePhotoRequest($photoData);
        self::assertResponseStatusCodeSame(201);
    }

    private function sendCreatePhotoRequest(array $photoData): Response
    {
        return $this->request('POST', self::ENDPOINT_PROJECT_PHOTOS, $photoData);
    }
}