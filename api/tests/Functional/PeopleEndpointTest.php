<?php
declare(strict_types=1);

namespace App\Tests\Functional;

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
        $response = $this->sendCreatePersonRequest('test', 'adam.daniel@test.com');
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
}