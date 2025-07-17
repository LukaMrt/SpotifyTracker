<?php

declare(strict_types=1);

namespace App\Command;

use App\Domain\Service\SpotifyConnectionService;
use SpotifyWebAPI\Session;
use Symfony\Component\Console\Attribute\Argument;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsCommand(
    name: 'spotify-tracker:tokens',
    description: 'Generate Spotify tokens from authorization code',
    help: 'This command allows you to generate Spotify tokens using the authorization code flow.',
)]
class GenerateSpotifyTokensCommand
{
    public function __construct(
        protected readonly Session $session,
        protected readonly SerializerInterface $serializer,
        protected readonly CacheInterface $cache
    ) {
    }

    public function __invoke(
        #[Argument(
            description: 'The authorization code received from Spotify.',
            name: 'code'
        )]
        string $code,
        SymfonyStyle $io,
    ): int
    {
        $io->title('Generate Spotify Tokens from authorization code');
        $io->note('Using authorization code: ' . $code);
        
        $this->cache->get(
            SpotifyConnectionService::CACHE_TOKENS_KEY,
            function (ItemInterface $item) use ($code): string {
                $this->session->requestAccessToken($code);
                $item->expiresAfter(SpotifyConnectionService::CACHE_TOKENS_EXPIRATION);
                return $this->serializer->serialize(
                    [
                        'access_token'  => $this->session->getAccessToken(),
                        'refresh_token' => $this->session->getRefreshToken(),
                    ],
                    'json'
                );
            }
        );
        $io->success('Spotify tokens generated and cached successfully.');
        return Command::SUCCESS;
    }
}
