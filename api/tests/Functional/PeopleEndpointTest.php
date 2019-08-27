<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Entity\Person;
use App\Entity\Project;
use App\Tests\ApiFunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class PeopleEndpointTest extends ApiFunctionalTestCase
{
    const ENDPOINT_PEOPLE   = '/people';

    public function testCreatePerson(): void
    {
        $fred = $this->getCreatedPerson('Test Fred', 'test.fred@email.com');
        $this->assertPersonHasAllAttributes($fred);

        self::assertEquals('Test Fred', $fred['name']);
        self::assertEquals([], $fred['ledProjects']);
    }

    public function testPersonEmailMustBeUnique(): void
    {
        $response = $this->sendCreatePersonRequest('test', 'daniel@fake.com');
        self::assertResponseStatusCodeSame(400);

        self::assertEquals(
            'email: This value is already used.',
            $this->hydraDescription($this->jsonDecode($response))
        );
    }

    public function testPeopleListIsNotEmpty(): void
    {
        $this->assertListIsNotEmpty(self::ENDPOINT_PEOPLE);
    }

    public function testAddingLedProjectWillRemoveProjectFromPreviousForeman(): void
    {
        $person1 = $this->getOneBy(Person::class, ['name' => 'Person1']);
        self::assertResponseIsSuccessful();
        $person2 = $this->getOneBy(Person::class, ['name' => 'Person2']);
        self::assertResponseIsSuccessful();
        $projectLedByPerson2 = $this->getOneBy(Project::class, ['name' => 'Project2']);
        self::assertResponseIsSuccessful();

        $this->assertIsLeadingProject($person2, $projectLedByPerson2);
        $this->assertIsNotLeadingProject($person1, $projectLedByPerson2);

        $person1['ledProjects'][] = $projectLedByPerson2['@id'];
        $updatedPerson1 = $this->putJson($person1['@id'], $person1);
        self::assertResponseIsSuccessful();

        $updatedProject = $this->getByIri($projectLedByPerson2['@id']);
        $updatedPerson2 = $this->getByIri($person2['@id']);
        $this->assertIsLeadingProject($updatedPerson1, $updatedProject);
        $this->assertIsNotLeadingProject($updatedPerson2, $updatedProject);
    }

    public function testRemovingLeadingProjectCannotLeaveProjectWithoutAForeman(): void
    {
        $person3 = $this->getOneBy(Person::class, ['name' => 'Person3']);
        self::assertResponseIsSuccessful();
        self::assertCount(1, $person3['ledProjects']);
        $projectLedByPerson3 = $this->getByIri(reset($person3['ledProjects']));
        $this->assertIsLeadingProject($person3, $projectLedByPerson3);

        $person3['ledProjects'] = [];
        $updatedPersonResponse = $this->putJson($person3['@id'], $person3);
        self::assertResponseStatusCodeSame(400);
        $this->assertEquals(
            'Cannot save project without a foreman',
            $this->hydraDescription($updatedPersonResponse)
        );
    }

    private function getCreatedPerson(string $name, string $email): array
    {
        $response = $this->sendCreatePersonRequest($name, $email);
        self::assertResponseStatusCodeSame(201);

        return $this->jsonDecode($response);
    }

    private function assertPersonHasAllAttributes(array $person): void
    {
        self::assertIsArray($person);
        self::assertArrayHasKey('@id', $person);
        $this->assertInternalIdNotExposed($person);
        self::assertArrayHasKey('name', $person);
        self::assertArrayHasKey('ledProjects', $person);
        self::assertArrayHasKey('email', $person);
    }

    protected function sendCreatePersonRequest(string $name, string $email): Response
    {
        return $this->request('POST', self::ENDPOINT_PEOPLE, ['name' => $name, 'email' => $email]);
    }

    private function assertIsNotLeadingProject(array $person, array $project): void
    {
        self::assertNotEquals($person['@id'], $project['foreman']);
        self::assertNotContains($project['@id'], $person['ledProjects']);
    }

    private function assertIsLeadingProject(array $person, array $project): void
    {
        self::assertEquals($person['@id'], $project['foreman']);
        self::assertContains($project['@id'], $person['ledProjects']);
    }
}
