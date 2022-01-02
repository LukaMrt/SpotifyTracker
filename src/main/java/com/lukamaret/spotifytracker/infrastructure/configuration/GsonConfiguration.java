package com.lukamaret.spotifytracker.infrastructure.configuration;

import com.google.gson.Gson;
import com.google.gson.GsonBuilder;
import com.lukamaret.spotifytracker.domain.application.configuration.DatabaseConfiguration;
import com.lukamaret.spotifytracker.domain.application.configuration.SpotifyConfiguration;
import com.lukamaret.spotifytracker.domain.model.configuration.Configuration;
import com.lukamaret.spotifytracker.domain.model.configuration.database.DatabaseCredentials;
import com.lukamaret.spotifytracker.domain.model.configuration.spotify.SpotifyCredentials;
import com.lukamaret.spotifytracker.domain.model.configuration.spotify.SpotifyTokens;

import java.io.File;
import java.io.FileNotFoundException;
import java.io.FileReader;
import java.io.FileWriter;

public class GsonConfiguration implements SpotifyConfiguration, com.lukamaret.spotifytracker.domain.application.configuration.DiscordConfiguration, DatabaseConfiguration {

    public static final Gson GSON = new GsonBuilder()
            .serializeNulls()
            .setPrettyPrinting()
            .create();

    private final Configuration configuration;
    private final File file;

    public GsonConfiguration(File file) {
        this.file = file;
        Configuration configuration = null;
        try {
            configuration = GSON.fromJson(new FileReader(file), Configuration.class);
        } catch (FileNotFoundException e) {
            e.printStackTrace();
            configuration = new Configuration();
        } finally {
            this.configuration = configuration;
        }
    }

    @Override
    public DatabaseCredentials getDatabaseCredentials() {
        return configuration.getDatabase();
    }

    @Override
    public String getToken() {
        return configuration.getDiscord().getToken();
    }

    @Override
    public String getLogsChannel() {
        return configuration.getDiscord().getLogs();
    }

    @Override
    public String getGuardId() {
        return configuration.getDiscord().getGuard();
    }

    @Override
    public SpotifyCredentials getSpotifyCredentials() {
        return configuration.getSpotify().getCredentials();
    }

    @Override
    public SpotifyTokens getSpotifyTokens() {
        return configuration.getSpotify().getTokens();
    }

    @Override
    public void setAccessToken(String accessToken) {
       configuration.getSpotify().getTokens().setAccessToken(accessToken);
    }

    @Override
    public void save() {

        try {
            FileWriter writer = new FileWriter(file);
            GSON.toJson(configuration, writer);
            writer.close();
        } catch (Exception e) {
            e.printStackTrace();
        }

    }

}
