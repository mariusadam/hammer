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
        $foremanIri = $this->findPersonIriWithoutProject();
        $projectData = [
            'name'        => 'Test project',
            'foreman'     => $foremanIri,
            'description' => 'This is a valid description',
        ];

        $response = $this->sendCreateProjectRequest($projectData);
        self::assertResponseStatusCodeSame(201);

        $project = $this->jsonDecode($response);
        $this->assertProjectHasAllAttributes($project);

        self::assertEquals($foremanIri, $project['foreman']);
    }

    /**
     * @dataProvider invalidDescriptionProvider
     */
    public function testCreateProjectWithInvalidDescriptionReturns400(string $invalidDescription): void
    {
        $response = $this->sendCreateProjectRequest(
            [
                'name'        => __METHOD__,
                'foreman'     => $this->findPersonIriWithoutProject(),
                'description' => $invalidDescription,
            ]
        );
        self::assertResponseStatusCodeSame(400);
        $decoded = $this->jsonDecode($response);
        self::assertContains(
            'description: ',
            $this->hydraDescription($decoded)
        );
    }

    public function invalidDescriptionProvider(): array
    {
        return [
            'too long'  => [str_repeat(' ', 10001)],
            'too short' => [str_repeat(' ', 9)],
            'empty'     => [''],
        ];
    }

    public function testProjectsListIsNotEmpty(): void
    {
        $this->assertListIsNotEmpty(self::ENDPOINT_PROJECTS);
    }

    public function testCanRetrieveProjectPhotos(): void
    {
        $projectIri = $this->findOneIriBy(Project::class, ['name' => 'ProjectLedByDaniel']);
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
        $this->assertInternalIdNotExposed($project);
        self::assertArrayHasKey('name', $project);
        self::assertArrayHasKey('foreman', $project);
        self::assertArrayHasKey('photos', $project);
        self::assertArrayHasKey('description', $project);
    }

    private function findPersonIriWithoutProject(): string
    {
        $foremanIri = $this->findOneIriBy(Person::class, ['name' => 'Simple worker']);

        return $foremanIri;
    }
}
