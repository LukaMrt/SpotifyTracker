<?php

declare(strict_types=1);

namespace App\Tests\Api;

use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Request;

#[Group('api')]
final class ArtistApiTest extends WebTestCase
{
    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        self::getClient(self::createClient());
    }

    private function getTestClient(): KernelBrowser
    {
        $client = self::getClient();
        $this->assertInstanceOf(KernelBrowser::class, $client);
        return $client;
    }

    /**
     * @return array<mixed>
     */
    private function getJsonResponse(): array
    {
        $content = $this->getTestClient()->getResponse()->getContent();
        $this->assertIsString($content);
        
        $decoded = json_decode($content, true);
        $this->assertIsArray($decoded);
        
        return $decoded;
    }

    public function testGetArtists_ShouldReturnExpectedArtistCount(): void
    {
        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/artists');

        // Then
        $this->assertResponseIsSuccessful();
        
        $artists = $this->getJsonResponse();
        $this->assertCount(14, $artists); // Based on fixtures
    }

    public function testGetArtists_ShouldIncludeSpecificKnownArtists(): void
    {
        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/artists');

        // Then
        $this->assertResponseIsSuccessful();
        
        $artists = $this->getJsonResponse();
        $artistNames = array_column($artists, 'name');
        
        // Verify specific artists from fixtures are present
        $this->assertContains('The Beatles', $artistNames);
        $this->assertContains('Carston', $artistNames);
        $this->assertContains('Rea Garvey', $artistNames);
    }

    public function testGetArtists_ShouldReturnArtistsSortedAlphabetically(): void
    {
        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/artists');

        // Then
        $this->assertResponseIsSuccessful();
        
        $artists = $this->getJsonResponse();
        $names = array_column($artists, 'name');
        $sortedNames = $names;
        sort($sortedNames, SORT_STRING | SORT_FLAG_CASE);
        
        $this->assertSame($sortedNames, $names);
    }

    public function testGetArtists_ShouldReturnFirstArtistAsAndreaToscano(): void
    {
        // When
        $this->getTestClient()->request(Request::METHOD_GET, '/api/artists');

        // Then
        $this->assertResponseIsSuccessful();
        
        $artists = $this->getJsonResponse();
        $this->assertNotEmpty($artists);
        $this->assertIsArray($artists[0]);
        $this->assertArrayHasKey('name', $artists[0]);
        $this->assertSame('Andrea Toscano', $artists[0]['name']);
    }
}