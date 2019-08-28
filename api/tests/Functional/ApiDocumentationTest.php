<?php
declare(strict_types=1);

namespace App\Tests\Functional;

use App\Tests\ApiFunctionalTestCase;

final class ApiDocumentationTest extends ApiFunctionalTestCase
{
    const JSON_LD_ENTRY_POINT
        = [
            '@context'     => '/contexts/Entrypoint',
            '@id'          => '/',
            '@type'        => 'Entrypoint',
            'person'       => PeopleEndpointTest::ENDPOINT_PEOPLE,
            'project'      => ProjectsEndpointTest::ENDPOINT_PROJECTS,
            'projectPhoto' => ProjectPhotosEndpointTest::ENDPOINT_PROJECT_PHOTOS,
            'image'        => ImagesEndpointTest::ENDPOINT_IMAGES,
        ];

    public function testRetrieveTheDocumentationAsHtml(): void
    {
        $response = $this->request('GET', '/', [], [self::ACCEPT => self::TEXT_HTML]);

        self::assertResponseStatusCodeSame(200);
        $this->assertContentType(self::TEXT_HTML, $response);

        $this->assertContains('Hammer', $response->getContent());
    }

    public function testRetrieveTheDocumentationAsLdJson(): void
    {
        $response = $this->request('GET', '/', [self::ACCEPT => self::APPLICATION_LD_JSON]);

        self::assertResponseStatusCodeSame(200);
        $this->assertContentTypeLdJson($response);

        $actual = $this->jsonDecode($response);
        self::assertEquals(self::JSON_LD_ENTRY_POINT, $actual);
    }
}
