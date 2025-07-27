<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use Symfony\Component\Validator\ConstraintViolationInterface;
use App\Domain\Spotify\Entity\Artist;
use App\Domain\Spotify\Entity\SpotifyId;
use App\Domain\Spotify\Entity\Track;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Group('integration')]
final class TrackTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
        $validator = static::getContainer()->get('validator');
        $this->assertInstanceOf(ValidatorInterface::class, $validator);
        $this->validator = $validator;
    }

    public function testTrackWithEmptyArtists_ShouldHaveValidationErrors(): void
    {
        // Given
        $spotifyId = SpotifyId::from('4dpARuHxo51G3z768sgnrY');
        $trackName = 'Hello';
        $emptyArtists = []; // Array vide

        // When
        $track = new Track($spotifyId, $trackName, $emptyArtists);
        $violations = $this->validator->validate($track);

        // Then
        $this->assertCount(1, $violations);
        $violation = $violations[0];
        $this->assertInstanceOf(ConstraintViolationInterface::class, $violation);
        $this->assertSame('Track must have at least one artist', $violation->getMessage());
        $this->assertSame('artists', $violation->getPropertyPath());
    }

    public function testTrackWithArtists_ShouldHaveNoValidationErrors(): void
    {
        // Given
        $spotifyId = SpotifyId::from('4dpARuHxo51G3z768sgnrY');
        $trackName = 'Hello';
        $artist = new Artist(SpotifyId::from('4dpARuHxo51G3z768sgnrA'), 'Adele');
        $artists = [$artist];

        // When
        $track = new Track($spotifyId, $trackName, $artists);
        $violations = $this->validator->validate($track);

        // Then
        $this->assertCount(0, $violations);
    }

    public function testTrackWithMultipleArtists_ShouldHaveNoValidationErrors(): void
    {
        // Given
        $spotifyId = SpotifyId::from('4dpARuHxo51G3z768sgnrY');
        $trackName = 'Collaboration Song';
        $artist1 = new Artist(SpotifyId::from('4dpARuHxo51G3z768sgnrA'), 'Artist 1');
        $artist2 = new Artist(SpotifyId::from('5epBRvIxp61G4z768sgnrZ'), 'Artist 2');
        $artists = [$artist1, $artist2];

        // When
        $track = new Track($spotifyId, $trackName, $artists);
        $violations = $this->validator->validate($track);

        // Then
        $this->assertCount(0, $violations);
    }
}