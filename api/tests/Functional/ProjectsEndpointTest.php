<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Person;
use App\Entity\Project;
use App\Tests\ApiFunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class ProjectsEndpointTest extends ApiFunctionalTestCase
{
    const ENDPOINT_PROJECTS = '/projects';

    public function testCreateProject(): void
    {
        $foremanIri = $this->findOneIriBy(Person::class, ['name' => 'Simple worker']);
        $projectData = [
            'name'    => 'Test project',
            'foreman' => $foremanIri,
        ];

        $response = $this->sendCreateProjectRequest($projectData);
        self::assertResponseStatusCodeSame(201);

        $project = $this->jsonDecode($response);
        $this->assertProjectHasAllAttributes($project);

        self::assertEquals($foremanIri, $project['foreman']);
    }

    public function testProjectsListIsNotEmpty(): void
    {
        $this->assertListIsNotEmpty(self::ENDPOINT_PROJECTS);
    }

    public function testCanRetrieveProjectPhotos(): void
    {
        $projectIri = $this->findOneIriBy(Project::class, ['name' => 'Project4']);
        $response = $this->request('GET', $projectIri.'/photos');
        self::assertResponseIsSuccessful();
        $photos = $this->hydraMember($this->jsonDecode($response));
        self::assertNotEmpty($photos);
    }

    private function sendCreateProjectRequest(array $projectData): Response
    {
        return $this->request('POST', self::ENDPOINT_PROJECTS, $projectData);
    }

    private function assertProjectHasAllAttributes(array $project): void
    {
        self::assertArrayHasKey('@id', $project);
        self::assertArrayHasKey('name', $project);
        self::assertArrayHasKey('foreman', $project);
        self::assertArrayHasKey('photos', $project);
    }
}
