<?php

declare(strict_types=1);

namespace App\Command;

use App\Handler\StoreListeningHandler;
use SpotifyWebAPI\Session;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsCommand(name: 'spotify-tracker:tokens')]
class GenerateSpotifyTokensCommand extends Command
{
    protected const int CACHE_TOKENS_EXPIRATION = 86_400; // 1 day

    public function __construct(
        protected readonly Session $session,
        protected readonly SerializerInterface $serializer,
        protected readonly CacheInterface $cache,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generate Spotify Tokens from authorization code')
            ->setHelp('This command allows you to generate Spotify tokens using the authorization code flow.')
            ->addArgument('code', InputArgument::REQUIRED, 'The authorization code received from Spotify.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Generate Spotify Tokens from authorization code');

        $code = $input->getArgument('code');
        $io->note('Using authorization code: ' . $code);

        $this->cache->get(
            StoreListeningHandler::CACHE_TOKENS_KEY,
            function (ItemInterface $item) use ($code) {
                $this->session->requestAccessToken($code);
                $item->expiresAfter(self::CACHE_TOKENS_EXPIRATION);
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
