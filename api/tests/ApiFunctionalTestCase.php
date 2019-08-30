<?php
declare(strict_types=1);

namespace App\Tests;

use App\Tests\Fixtures\ImageFactory;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;

/**
 * Base class which provides utility functions for all functional tests
 */
class ApiFunctionalTestCase extends WebTestCase
{
    use RefreshDatabaseTrait;

    /**
     * Header names
     */
    const ACCEPT       = 'Accept';
    const CONTENT_TYPE = 'Content-Type';

    /**
     * Content types
     */
    const TEXT_HTML           = 'text/html';
    const APPLICATION_LD_JSON = 'application/ld+json';
    const MULTIPART_FORM_DATA = 'multipart/form-data';

    /** @var KernelBrowser */
    private $browser;

    /** @var Filesystem */
    private static $filesystem;

    private static $filesToRemove = [];

    public static function setUpBeforeClass()
    {
        parent::tearDownAfterClass();
        self::$filesystem = new Filesystem();
        // override image factory here to not require internet connection when running tests
        ImageFactory::setImageFilePathFactory(
            function () {
                // because the path to the files here might be remove we need to copy
                // the fixture file to tmp and return that path

                $tmpPath = tempnam(sys_get_temp_dir(), 'test-image');
                $tmpPathWithExtension = sprintf('%s.png', $tmpPath);
                self::$filesystem->remove($tmpPath);
                self::$filesystem->copy(__DIR__.'/Fixtures/test-image.png', $tmpPathWithExtension);

                return self::$filesToRemove[] = $tmpPathWithExtension;
            }
        );
    }

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();

        self::tearDownFixtureFiles();
        self::$filesystem = null;
        ImageFactory::unsetImageFactory();
    }

    public static function tearDownFixtureFiles(): void
    {
        $testImages = [];
        $mediaDirectoryIterator = new \FilesystemIterator(__DIR__.'/../public/media/images');
        foreach ($mediaDirectoryIterator as $file) {
            assert($file instanceof \SplFileInfo);
            if (ImageFactory::isFixture($file)) {
                $testImages[] = $file->getRealPath();
            }
        }

        self::$filesystem->remove($testImages + self::$filesToRemove);
        self::$filesToRemove = [];
    }

    protected function setUp()
    {
        parent::setUp();

        $this->browser = static::createClient();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->browser = null;
    }

    protected function request(string $method, string $uri, array $content = [], array $headers = []): Response
    {
        $server = $this->createServer($headers);

        $content = $this->encodeContent($server['CONTENT_TYPE'], $content);
        $this->browser->request($method, $uri, [], [], $server, $content);

        return $this->browser->getResponse();
    }

    protected function getBrowser(): KernelBrowser
    {
        return $this->browser;
    }

    private function encodeContent(string $contentType, array $content): ?string
    {
        switch ($contentType) {
            case self::APPLICATION_LD_JSON:
                return json_encode($content);
            default:
                return null;
        }
    }

    protected function jsonDecode(Response $response): array
    {
        $json = json_decode($response->getContent(), true);
        self::assertIsArray($json);

        return $json;
    }

    protected function findOneIriBy(string $resourceClass, array $criteria = []): string
    {
        $resource = static::$container->get('doctrine')->getRepository($resourceClass)->findOneBy($criteria);

        return static::$container->get('api_platform.iri_converter')->getIriFromitem($resource);
    }

    protected function getOneBy(string $resourceClass, array $criteria = []): array
    {
        $iriBy = $this->findOneIriBy($resourceClass, $criteria);

        return $this->getByIri($iriBy);
    }

    protected function getByIri(string $iri): array
    {
        $response = $this->request('GET', $iri);
        self::assertResponseIsSuccessful();

        return $this->jsonDecode($response);
    }

    protected function assertNotFound(string $iri): void
    {
        $this->request('GET', $iri);
        self::assertResponseStatusCodeSame(404);
    }

    protected function delete(string $iri): void
    {
        $this->request('DELETE', $iri);
        self::assertResponseStatusCodeSame(204);
    }

    protected function putJson(string $iri, array $updatedData): array
    {
        return $this->jsonDecode($this->request('PUT', $iri, $updatedData));
    }

    protected function hydraMember(array $json): array
    {
        $member = $this->arrayGet($json, 'hydra:member');
        self::assertIsArray($member);

        return $member;
    }

    protected function hydraTotalItems(array $json): int
    {
        $total = $this->arrayGet($json, 'hydra:totalItems');
        self::assertIsInt($total);

        return $total;
    }

    protected function hydraDescription(array $json): string
    {
        $description = $this->arrayGet($json, 'hydra:description');
        self::assertIsString($description);

        return $description;
    }

    protected function arrayGet(array $array, string $key)
    {
        self::assertArrayHasKey($key, $array);

        return $array[$key];
    }

    protected function singleValueDataSet(...$values): array
    {
        $toArray = function ($val) {
            return (array)$val;
        };

        return array_combine($values, array_map($toArray, $values));
    }

    protected function assertContentTypeLdJson(Response $response): void
    {
        $this->assertContentType(self::APPLICATION_LD_JSON, $response);
    }
    protected function assertContentType(string $expected, Response $response): void
    {
        $expected .= '; charset=utf-8';
        $this->assertEquals($expected, strtolower($response->headers->get(self::CONTENT_TYPE)));

    }

    public function assertListIsNotEmpty(string $listUri): void
    {
        $response = $this->request('GET', $listUri);
        $json = $this->jsonDecode($response);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertContentTypeLdJson($response);

        $this->assertGreaterThan(0, $this->hydraTotalItems($json));
        $this->assertNotEmpty($this->hydraMember($json));
    }

    protected function assertInternalIdNotExposed(array $entity): void
    {
        self::assertArrayNotHasKey('id', $entity, 'Internal entity identifier should not be exposed');
    }

    protected function createServer(array $headers): array
    {
        $server = ['CONTENT_TYPE' => self::APPLICATION_LD_JSON, 'HTTP_ACCEPT' => self::APPLICATION_LD_JSON];
        foreach ($headers as $key => $value) {
            if (strtolower($key) === strtolower(self::CONTENT_TYPE)) {
                $server['CONTENT_TYPE'] = $value;

                continue;
            }

            $server['HTTP_'.strtoupper(str_replace('-', '_', $key))] = $value;
        }

        return $server;
    }
}
