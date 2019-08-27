<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Image;
use App\Entity\Project;
use App\Tests\ApiFunctionalTestCase;

final class ProjectPhotosEndpointTest extends ApiFunctionalTestCase
{
    public const ENDPOINT_PROJECT_PHOTOS = '/project_photos';

    public function testCannotCreateProjectPhotoWithoutProject()
    {
        $photoData = [
            'photo'            => $this->findOneIriBy(Image::class, []),
            'shortDescription' => str_repeat(' ', 10),
        ];

        $json = $this->postPhoto($photoData);
        self::assertResponseStatusCodeSame(400);
        self::assertEquals(
            'project: This value should not be null.',
            $this->hydraDescription($json)
        );
    }

    public function testCannotCreateProjectPhotoWithoutImage()
    {
        $photoData = [
            'project'          => $this->findOneIriBy(Project::class, []),
            'shortDescription' => str_repeat(' ', 10),
        ];

        $json = $this->postPhoto($photoData);
        self::assertResponseStatusCodeSame(400);
        self::assertEquals(
            'photo: This value should not be null.',
            $this->hydraDescription($json)
        );
    }

    public function testCannotCreateProjectPhotoWithInvalidShortDescription()
    {
        $onlyPhotoAndProject = [
            'photo'   => $this->findOneIriBy(Image::class, []),
            'project' => $this->findOneIriBy(Project::class, []),
        ];

        self::assertStringContainsString(
            'shortDescription: ',
            $this->hydraDescription($this->postPhoto($onlyPhotoAndProject))
        );
        self::assertResponseStatusCodeSame(400);

        self::assertStringContainsString(
            'shortDescription: This value is too short',
            $this->hydraDescription($this->postPhoto($onlyPhotoAndProject + ['shortDescription' => 'too short']))
        );
        self::assertStringContainsString(
            'shortDescription: This value is too long',
            $this->hydraDescription(
                $this->postPhoto($onlyPhotoAndProject + ['shortDescription' => str_repeat(' ', 10000)])
            )
        );
    }

    private function postPhoto(array $photoData): array
    {
        return $this->jsonDecode($this->request('POST', self::ENDPOINT_PROJECT_PHOTOS, $photoData));
    }
}
