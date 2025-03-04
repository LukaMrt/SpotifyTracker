<?php

declare(strict_types=1);

namespace App\Controller;

use SpotifyWebAPI\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/callback')]
class SpotifyCallbackController extends AbstractController
{
    public function __construct(
        protected readonly Session $session,
        protected readonly SerializerInterface $serializer,
        protected readonly CacheInterface $cache,
    ) {
    }

    #[Route('/login', name: 'spotify_tracker.spotify_login_callback')]
    public function index(#[MapQueryParameter] string $code): Response
    {
        $tokens = $this->cache->get(
            'spotify_tokens',
            function (ItemInterface $item) use ($code) {
                $this->session->requestAccessToken($code);
                $item->expiresAfter(86_400); // 1 day
                return $this->serializer->serialize(
                    [
                        'access_token'  => $this->session->getAccessToken(),
                        'refresh_token' => $this->session->getRefreshToken(),
                    ],
                    'json'
                );
            }
        );

        return new Response(
            'Spotify tokens stored in cache',
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain']
        );
    }
}
