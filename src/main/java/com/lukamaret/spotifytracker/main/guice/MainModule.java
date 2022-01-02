package com.lukamaret.spotifytracker.main.guice;

import com.google.inject.AbstractModule;
import com.lukamaret.spotifytracker.domain.application.configuration.DatabaseConfiguration;
import com.lukamaret.spotifytracker.domain.application.configuration.DiscordConfiguration;
import com.lukamaret.spotifytracker.domain.application.configuration.SpotifyConfiguration;
import com.lukamaret.spotifytracker.domain.application.message.MessageSender;
import com.lukamaret.spotifytracker.domain.application.spotify.*;
import com.lukamaret.spotifytracker.infrastructure.database.DatabaseConnection;
import com.lukamaret.spotifytracker.infrastructure.spotify.PostgresArtistsRepository;
import com.lukamaret.spotifytracker.infrastructure.spotify.PostgresListeningRepository;
import com.lukamaret.spotifytracker.infrastructure.spotify.PostgresPlaylistRepository;
import com.lukamaret.spotifytracker.infrastructure.spotify.PostgresTrackRepository;
import com.lukamaret.spotifytracker.view.message.DiscordMessageSender;
import com.wrapper.spotify.SpotifyApi;
import org.javacord.api.DiscordApi;

public class MainModule extends AbstractModule {

    private final DiscordApi discord;
    private final SpotifyApi spotify;
    private final DatabaseConnection databaseConnection;
    private final SpotifyConfiguration spotifyConfiguration;
    private final DatabaseConfiguration databaseConfiguration;
    private final DiscordConfiguration discordConfiguration;

    public MainModule(DiscordApi discord,
                      SpotifyApi spotify,
                      DatabaseConnection databaseConnection,
                      SpotifyConfiguration spotifyConfiguration,
                      DatabaseConfiguration databaseConfiguration,
                      DiscordConfiguration discordConfiguration) {
        this.discord = discord;
        this.spotify = spotify;
        this.databaseConnection = databaseConnection;
        this.spotifyConfiguration = spotifyConfiguration;
        this.databaseConfiguration = databaseConfiguration;
        this.discordConfiguration = discordConfiguration;
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
        bind(TrackRepository.class).to(PostgresTrackRepository.class);
        bind(PlaylistRepository.class).to(PostgresPlaylistRepository.class);
        bind(ArtistsRepository.class).to(PostgresArtistsRepository.class);
        bind(ListeningRepository.class).to(PostgresListeningRepository.class);
        bind(MessageSender.class).to(DiscordMessageSender.class);
    }

}
