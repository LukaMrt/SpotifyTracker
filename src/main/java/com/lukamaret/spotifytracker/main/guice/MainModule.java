package com.lukamaret.spotifytracker.main.guice;

import com.google.inject.AbstractModule;
import com.google.inject.Injector;
import com.lukamaret.spotifytracker.domain.application.clean.ChannelCleaner;
import com.lukamaret.spotifytracker.domain.application.configuration.DatabaseConfiguration;
import com.lukamaret.spotifytracker.domain.application.configuration.DiscordConfiguration;
import com.lukamaret.spotifytracker.domain.application.configuration.SpotifyConfiguration;
import com.lukamaret.spotifytracker.domain.application.message.MessageSender;
import com.lukamaret.spotifytracker.domain.application.spotify.*;
import com.lukamaret.spotifytracker.infrastructure.clean.DiscordChannelCleaner;
import com.lukamaret.spotifytracker.infrastructure.database.DatabaseConnection;
import com.lukamaret.spotifytracker.infrastructure.database.DatabaseConnectionBuilder;
import com.lukamaret.spotifytracker.infrastructure.spotify.MariadbArtistsRepository;
import com.lukamaret.spotifytracker.infrastructure.spotify.MariadbListeningRepository;
import com.lukamaret.spotifytracker.infrastructure.spotify.MariadbPlaylistRepository;
import com.lukamaret.spotifytracker.infrastructure.spotify.MariadbTrackRepository;
import com.lukamaret.spotifytracker.view.message.DiscordMessageSender;
import org.javacord.api.DiscordApi;
import org.javacord.api.DiscordApiBuilder;
import se.michaelthelin.spotify.SpotifyApi;

public class MainModule extends AbstractModule {

    private final DiscordApi discord;
    private final SpotifyApi spotify;
    private final DatabaseConnection databaseConnection;
    private final SpotifyConfiguration spotifyConfiguration;
    private final DatabaseConfiguration databaseConfiguration;
    private final DiscordConfiguration discordConfiguration;

    public MainModule(Injector configurationInjector) {

        this.spotifyConfiguration = configurationInjector.getInstance(SpotifyConfiguration.class);
        this.databaseConfiguration = configurationInjector.getInstance(DatabaseConfiguration.class);
        this.discordConfiguration = configurationInjector.getInstance(DiscordConfiguration.class);

        this.discord = new DiscordApiBuilder()
                .setToken(discordConfiguration.getToken())
                .login()
                .join();

        this.spotify = new SpotifyApi.Builder()
                .setClientId(spotifyConfiguration.getSpotifyCredentials().getId())
                .setClientSecret(spotifyConfiguration.getSpotifyCredentials().getSecret())
                .setAccessToken(spotifyConfiguration.getSpotifyTokens().getAccessToken())
                .setRefreshToken(spotifyConfiguration.getSpotifyTokens().getRefreshToken())
                .build();

        this.databaseConnection = DatabaseConnectionBuilder
                .aDatabaseConnection()
                .withHost(databaseConfiguration.getDatabaseCredentials().getHost())
                .withPort(databaseConfiguration.getDatabaseCredentials().getPort())
                .withUser(databaseConfiguration.getDatabaseCredentials().getUser())
                .withPassword(databaseConfiguration.getDatabaseCredentials().getPassword())
                .withDatabase(databaseConfiguration.getDatabaseCredentials().getDatabase())
                .build();
    }

    @Override
    public void configure() {
        bind(DiscordApi.class).toInstance(discord);
        bind(SpotifyApi.class).toInstance(spotify);
        bind(DatabaseConnection.class).toInstance(databaseConnection);
        bind(SpotifyConfiguration.class).toInstance(spotifyConfiguration);
        bind(DatabaseConfiguration.class).toInstance(databaseConfiguration);
        bind(DiscordConfiguration.class).toInstance(discordConfiguration);
        bind(TrackService.class);
        bind(TrackRepository.class).to(MariadbTrackRepository.class);
        bind(PlaylistRepository.class).to(MariadbPlaylistRepository.class);
        bind(ArtistsRepository.class).to(MariadbArtistsRepository.class);
        bind(ListeningRepository.class).to(MariadbListeningRepository.class);
        bind(MessageSender.class).to(DiscordMessageSender.class);
        bind(ChannelCleaner.class).to(DiscordChannelCleaner.class);
    }

}
