<?php

declare(strict_types=1);

namespace App\Controller;

use SpotifyWebAPI\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

#[Route('/')]
class HomeController extends AbstractController
{
    public function __construct(
        protected readonly Session $session,
        protected readonly SerializerInterface $serializer,
        protected readonly CacheInterface $cache,
    ) {
    }

    #[Route('', name: 'spotify_tracker.home')]
    public function index(): Response
    {
        return new JsonResponse(
            'Spotify tracker',
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain']
        );
    }
}
