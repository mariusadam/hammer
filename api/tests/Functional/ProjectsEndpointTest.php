<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Person;
use App\Entity\Project;
use App\Tests\ApiFunctionalTestCase;
use foo\bar;

final class ProjectsEndpointTest extends ApiFunctionalTestCase
{
    const ENDPOINT_PROJECTS = '/projects';

    public function testCreateProjectWithoutPhotos(): void
    {
        $foremanIri = $this->findPersonIriWithoutProject();
        $projectData = [
            'name'        => 'Test project',
            'foreman'     => $foremanIri,
            'description' => 'This is a valid description',
        ];

        $project = $this->postProject($projectData);
        self::assertResponseStatusCodeSame(201);

        $this->assertProjectHasAllAttributes($project);

        self::assertEquals($foremanIri, $project['foreman']);
    }

    /**
     * @dataProvider invalidDescriptionProvider
     */
    public function testCreateProjectWithInvalidDescriptionReturns400(string $invalidDescription): void
    {
        $json = $this->postProject(
            [
                'name'        => __METHOD__,
                'foreman'     => $this->findPersonIriWithoutProject(),
                'description' => $invalidDescription,
            ]
        );
        self::assertResponseStatusCodeSame(400);
        self::assertContains(
            'description: ',
            $this->hydraDescription($json)
        );
    }

    public function testCannotCreateProjectWithoutAForeman(): void
    {
        $json = $this->postProject(
            [
                'name'        => __METHOD__,
                'description' => str_repeat(' ', 10),
            ]
        );
        self::assertResponseStatusCodeSame(400);
        self::assertEquals(
            'foreman: This value should not be null.',
            $this->hydraDescription($json)
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
        $projectIri = $this->findProjectIriByName('ProjectLedByDaniel');
        $response = $this->request('GET', $projectIri.'/photos');
        self::assertResponseIsSuccessful();
        $photos = $this->hydraMember($this->jsonDecode($response));
        self::assertNotEmpty($photos);
    }

    public function testRemovingProjectWillAlsoRemoveRelatedPhotosButNotForeman(): void
    {
        $projectIri = $this->findProjectIriByName('Project4');
        $project = $this->getByIri($projectIri);
        $photosIris = $project['photos'];
        $foremanIri = $project['foreman'];
        $foreman = $this->getByIri($foremanIri);

        self::assertContains($projectIri, $foreman['ledProjects']);
        self::assertNotEmpty($photosIris);
        // ensure all photos can be retrieved before the deleted
        array_walk($photosIris, [$this, 'getByIri']);

        $this->delete($projectIri);
        $updatedForeman = $this->getByIri($foremanIri);
        self::assertNotContains($projectIri, $updatedForeman['ledProjects']);

        $this->assertNotFound($projectIri);
        // photos should be also removed after removing the project
        array_walk($photosIris, [$this, 'assertNotFound']);
    }

    public function testAddingNewPhotoWillUnAssignPhotosFromPreviousProject(): void
    {
        $projectToUpdate = $this->getByIri($this->findProjectIriByName('Project5'));
        $projectToRemovePhotosFrom = $this->getByIri($this->findProjectIriByName('Project6'));
        self::assertNotEmpty($projectToUpdate['photos']);
        self::assertNotEmpty($projectToRemovePhotosFrom['photos']);

        $projectToUpdate['photos'] = array_merge($projectToUpdate['photos'], $projectToRemovePhotosFrom['photos']);
        $updatedProject = $this->putJson($projectToUpdate['@id'], $projectToUpdate);
        $projectWithRemovedPhotos = $this->getByIri($projectToRemovePhotosFrom['@id']);

        foreach ($projectToUpdate['photos'] as $photoIri) {
            self::assertContains($photoIri, $updatedProject['photos']);
        }
        self::assertCount(0, $projectWithRemovedPhotos['photos']);
    }

    public function testCannotRemovePhotoBecauseThatWouldLeaveAPhotoWithoutAProject(): void
    {
        $projectToRemovePhotoFrom = $this->getByIri($this->findProjectIriByName('Project7'));
        self::assertNotEmpty($projectToRemovePhotoFrom['photos']);
        $projectToRemovePhotoFrom['photos'] = [];
        $json = $this->putJson($projectToRemovePhotoFrom['@id'], $projectToRemovePhotoFrom);
        self::assertResponseStatusCodeSame(400);
        self::assertEquals(
            'Cannot save project photos without being assigned to a project',
            $this->hydraDescription($json)
        );
    }

    private function postProject(array $projectData): array
    {
        return $this->jsonDecode($this->request('POST', self::ENDPOINT_PROJECTS, $projectData));
    }

    private function findProjectIriByName(string $name): string
    {
        return $this->findOneIriBy(Project::class, ['name' => $name]);
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
