<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Application\DTO\ArtistDto;
use App\Domain\Spotify\Repository\ArtistRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;

final class ArtistController extends AbstractController
{
    public function __construct(
        private readonly ArtistRepositoryInterface $artistRepository,
        private readonly ObjectMapperInterface $objectMapper,
    ) {}

    #[Route(
        path: '/api/artists',
        name: 'api_artists_list',
        methods: [Request::METHOD_GET],
    )]
    public function getArtists(): JsonResponse
    {
        $artists = $this->artistRepository->findAllWithListenings();

        $artistDtos = array_map(
            fn($artist): object => $this->objectMapper->map($artist, ArtistDto::class),
            $artists
        );

        return $this->json($artistDtos);
    }
}
