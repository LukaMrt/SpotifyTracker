<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain;

use App\Domain\Spotify\Entity\SpotifyId;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Group('integration')]
final class SpotifyIdTest extends KernelTestCase
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

    public function testSpotifyIdWithInvalidFormat_ShouldHaveValidationErrors(): void
    {
        // Given
        $spotifyId = new SpotifyId('invalid-id'); // Trop court, caractères invalides

        // When
        $violations = $this->validator->validate($spotifyId);

        // Then
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testSpotifyIdWithValidFormat_ShouldHaveNoValidationErrors(): void
    {
        // Given
        $validId = '4dpARuHxo51G3z768sgnrY'; // 22 caractères alphanumériques
        $spotifyId = SpotifyId::from($validId);

        // When
        $violations = $this->validator->validate($spotifyId);

        // Then
        $this->assertCount(0, $violations);
        $this->assertSame($validId, (string) $spotifyId);
    }

    public function testSpotifyIdEquals_ShouldReturnTrueForSameId(): void
    {
        // Given
        $id1 = SpotifyId::from('4dpARuHxo51G3z768sgnrY');
        $id2 = SpotifyId::from('4dpARuHxo51G3z768sgnrY');

        // When & Then
        $this->assertTrue($id1->equals($id2));
    }

    public function testSpotifyIdEquals_ShouldReturnFalseForDifferentId(): void
    {
        // Given
        $id1 = SpotifyId::from('4dpARuHxo51G3z768sgnrY');
        $id2 = SpotifyId::from('5epBRvIxp61G4z768sgnrZ');

        // When & Then
        $this->assertFalse($id1->equals($id2));
    }

    public function testSpotifyIdWithBlankValue_ShouldHaveValidationErrors(): void
    {
        // Given
        $spotifyId = new SpotifyId('');

        // When
        $violations = $this->validator->validate($spotifyId);

        // Then
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testSpotifyIdWithTooShortValue_ShouldHaveValidationErrors(): void
    {
        // Given
        $spotifyId = new SpotifyId('4dpARuHxo51G3z768sgn'); // 21 caractères seulement

        // When
        $violations = $this->validator->validate($spotifyId);

        // Then
        $this->assertGreaterThan(0, $violations->count());
    }

    public function testSpotifyIdWithTooLongValue_ShouldHaveValidationErrors(): void
    {
        // Given
        $spotifyId = new SpotifyId('4dpARuHxo51G3z768sgnrYZ'); // 23 caractères

        // When
        $violations = $this->validator->validate($spotifyId);

        // Then
        $this->assertGreaterThan(0, $violations->count());
    }
}