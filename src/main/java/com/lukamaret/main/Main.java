package com.lukamaret.main;

import com.google.inject.Guice;
import com.google.inject.Injector;
import com.lukamaret.domain.application.configuration.DatabaseConfiguration;
import com.lukamaret.domain.application.configuration.DiscordConfiguration;
import com.lukamaret.domain.application.configuration.SpotifyConfiguration;
import com.lukamaret.infrastructure.database.DatabaseConnection;
import com.lukamaret.infrastructure.database.DatabaseConnectionBuilder;
import com.lukamaret.infrastructure.listeners.DiscordListenersRepository;
import com.lukamaret.infrastructure.tasks.DefaultTasksRepository;
import com.lukamaret.main.guice.ConfigurationModule;
import com.lukamaret.main.guice.MainModule;
import com.wrapper.spotify.SpotifyApi;
import org.javacord.api.DiscordApi;
import org.javacord.api.DiscordApiBuilder;
import org.slf4j.LoggerFactory;

import java.io.File;

public class Main {

    public static void main(String[] args) {

        File configFile = new File("configuration.json");

        Injector configurationInjector = Guice.createInjector(new ConfigurationModule(configFile));

        DiscordConfiguration discordConfiguration = configurationInjector.getInstance(DiscordConfiguration.class);
        SpotifyConfiguration spotifyConfiguration = configurationInjector.getInstance(SpotifyConfiguration.class);
        DatabaseConfiguration databaseConfiguration = configurationInjector.getInstance(DatabaseConfiguration.class);

        DiscordApi discord = new DiscordApiBuilder()
                .setToken(discordConfiguration.getToken())
                .login()
                .join();

        SpotifyApi spotify = new SpotifyApi.Builder()
                .setClientId(spotifyConfiguration.getSpotifyCredentials().getId())
                .setClientSecret(spotifyConfiguration.getSpotifyCredentials().getSecret())
                .setAccessToken(spotifyConfiguration.getSpotifyTokens().getAccessToken())
                .setRefreshToken(spotifyConfiguration.getSpotifyTokens().getRefreshToken())
                .build();

        DatabaseConnection database = DatabaseConnectionBuilder
                .aDatabaseConnection()
                .withHost(databaseConfiguration.getDatabaseCredentials().getHost())
                .withPort(databaseConfiguration.getDatabaseCredentials().getPort())
                .withUser(databaseConfiguration.getDatabaseCredentials().getUser())
                .withPassword(databaseConfiguration.getDatabaseCredentials().getPassword())
                .withDatabase(databaseConfiguration.getDatabaseCredentials().getDatabase())
                .build();

        Injector injector = Guice.createInjector(new MainModule(discord, spotify, database, spotifyConfiguration, databaseConfiguration));

        new DiscordListenersRepository(injector).registerListeners();
        new DefaultTasksRepository(injector).registerTasks();

        LoggerFactory.getLogger(Main.class).info("Spotify tracker started");
    }

}
