package com.lukamaret.spotifytracker.domain.model.configuration;

import com.lukamaret.spotifytracker.domain.model.configuration.database.DatabaseCredentials;
import com.lukamaret.spotifytracker.domain.model.configuration.discord.DiscordConfiguration;
import com.lukamaret.spotifytracker.domain.model.configuration.spotify.SpotifyConfiguration;

public class Configuration {

    private SpotifyConfiguration spotify = new SpotifyConfiguration();
    private DiscordConfiguration discord = new DiscordConfiguration();
    private DatabaseCredentials database = new DatabaseCredentials();

    public SpotifyConfiguration getSpotify() {
        return spotify;
    }

    public DiscordConfiguration getDiscord() {
        return discord;
    }

    public DatabaseCredentials getDatabase() {
        return database;
    }

}
