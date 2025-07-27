<?php

declare(strict_types=1);

namespace App\Tests\Integration\Application;

use App\Application\DTO\ArtistDto;
use App\Domain\Spotify\Entity\Artist;
use App\Domain\Spotify\Entity\SpotifyId;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;

#[Group('integration')]
final class ArtistDtoTest extends KernelTestCase
{
    private ObjectMapperInterface $objectMapper;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        $objectMapper = self::getContainer()->get(ObjectMapperInterface::class);
        $this->assertInstanceOf(ObjectMapperInterface::class, $objectMapper);
        $this->objectMapper = $objectMapper;
    }

    public function testCreate_WithValidData_ShouldInstantiate(): void
    {
        // Given
        $id = 'spotify123456789012345678';
        $name = 'The Beatles';

        // When
        $dto = new ArtistDto($id, $name);

        // Then
        $this->assertSame($id, $dto->id);
        $this->assertSame($name, $dto->name);
    }

    public function testReadOnlyProperties_ShouldNotBeModifiable(): void
    {
        // Given
        $dto = new ArtistDto('spotify123456789012345678', 'The Beatles');
        
        // Verify properties are readonly
        $reflection = new \ReflectionClass($dto);
        $idProperty = $reflection->getProperty('id');
        $nameProperty = $reflection->getProperty('name');
        
        $this->assertTrue($idProperty->isReadOnly());
        $this->assertTrue($nameProperty->isReadOnly());
    }

    public function testObjectMapper_WithArtist_ShouldMapToArtistDto(): void
    {
        // Given
        $spotifyId = new SpotifyId('spotify123456789012345678');
        $artist = new Artist($spotifyId, 'The Beatles');

        // When
        $dto = $this->objectMapper->map($artist, ArtistDto::class);

        // Then
        $this->assertInstanceOf(ArtistDto::class, $dto);
        $this->assertSame('spotify123456789012345678', $dto->id);
        $this->assertSame('The Beatles', $dto->name);
    }
}