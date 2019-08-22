<?php
declare(strict_types=1);

namespace App\Tests;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ApiTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    /** @var KernelBrowser */
    protected $browser;

    /**
     * Retrieves the building list.
     */
    public function testRetrieveTheBuildingist(): void
    {
        $response = $this->request('GET', '/buildings');
        $json = json_decode($response->getContent(), true);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/ld+json; charset=utf-8', $response->headers->get('Content-Type'));

        $this->assertArrayHasKey('hydra:totalItems', $json);
        $this->assertEquals(5, $json['hydra:totalItems']);

        $this->assertArrayHasKey('hydra:member', $json);
        $this->assertCount(5, $json['hydra:member']);
    }

    /**
     * Retrieves the documentation.
     */
    public function testRetrieveTheDocumentation(): void
    {
        $response = $this->request('GET', '/', null, ['Accept' => 'text/html']);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', $response->headers->get('Content-Type'));

        $this->assertContains('Hammer', $response->getContent());
    }

    protected function setUp()
    {
        parent::setUp();

        $this->browser = static::createClient();
    }

    /**
     * @param string|array|null $content
     */
    protected function request(string $method, string $uri, $content = null, array $headers = []): Response
    {
        $server = ['CONTENT_TYPE' => 'application/ld+json', 'HTTP_ACCEPT' => 'application/ld+json'];
        foreach ($headers as $key => $value) {
            if (strtolower($key) === 'content-type') {
                $server['CONTENT_TYPE'] = $value;

                continue;
            }

            $server['HTTP_'.strtoupper(str_replace('-', '_', $key))] = $value;
        }

        if (is_array($content) && false !== preg_match('#^application/(?:.+\+)?json$#', $server['CONTENT_TYPE'])) {
            $content = json_encode($content);
        }

        $this->browser->request($method, $uri, [], [], $server, $content);

        return $this->browser->getResponse();
    }

    protected function findOneIriBy(string $resourceClass, array $criteria): string
    {
        $resource = static::$container->get('doctrine')->getRepository($resourceClass)->findOneBy($criteria);

        return static::$container->get('api_platform.iri_converter')->getIriFromitem($resource);
    }
}