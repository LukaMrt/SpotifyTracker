package com.lukamaret.main.guice;

import com.google.inject.AbstractModule;
import com.lukamaret.domain.application.configuration.DatabaseConfiguration;
import com.lukamaret.domain.application.configuration.SpotifyConfiguration;
import com.lukamaret.domain.application.spotify.*;
import com.lukamaret.infrastructure.database.DatabaseConnection;
import com.lukamaret.infrastructure.spotify.PostgresArtistsRepository;
import com.lukamaret.infrastructure.spotify.PostgresListeningRepository;
import com.lukamaret.infrastructure.spotify.PostgresPlaylistRepository;
import com.lukamaret.infrastructure.spotify.PostgresTrackRepository;
import com.wrapper.spotify.SpotifyApi;
import org.javacord.api.DiscordApi;

public class MainModule extends AbstractModule {

    private final DiscordApi discord;
    private final SpotifyApi spotify;
    private final DatabaseConnection databaseConnection;
    private final SpotifyConfiguration spotifyConfiguration;
    private final DatabaseConfiguration databaseConfiguration;

    public MainModule(DiscordApi discord,
                      SpotifyApi spotify,
                      DatabaseConnection databaseConnection,
                      SpotifyConfiguration spotifyConfiguration,
                      DatabaseConfiguration databaseConfiguration) {
        this.discord = discord;
        this.spotify = spotify;
        this.databaseConnection = databaseConnection;
        this.spotifyConfiguration = spotifyConfiguration;
        this.databaseConfiguration = databaseConfiguration;
    }

    @Override
    public void configure() {
        bind(DiscordApi.class).toInstance(discord);
        bind(SpotifyApi.class).toInstance(spotify);
        bind(DatabaseConnection.class).toInstance(databaseConnection);
        bind(SpotifyConfiguration.class).toInstance(spotifyConfiguration);
        bind(DatabaseConfiguration.class).toInstance(databaseConfiguration);
        bind(TrackService.class);
        bind(TrackRepository.class).to(PostgresTrackRepository.class);
        bind(PlaylistRepository.class).to(PostgresPlaylistRepository.class);
        bind(ArtistsRepository.class).to(PostgresArtistsRepository.class);
        bind(ListeningRepository.class).to(PostgresListeningRepository.class);
    }

}
