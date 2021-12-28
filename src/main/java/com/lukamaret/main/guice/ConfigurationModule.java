package com.lukamaret.main.guice;

import com.google.inject.AbstractModule;
import com.lukamaret.domain.application.configuration.DatabaseConfiguration;
import com.lukamaret.domain.application.configuration.DiscordConfiguration;
import com.lukamaret.domain.application.configuration.SpotifyConfiguration;
import com.lukamaret.infrastructure.configuration.GsonConfiguration;

import java.io.File;

public class ConfigurationModule extends AbstractModule {

    private final File configFile;

    public ConfigurationModule(File configFile) {
        this.configFile = configFile;
    }

    @Override
    protected void configure() {
        bind(SpotifyConfiguration.class).toInstance(new GsonConfiguration(configFile));
        bind(DiscordConfiguration.class).toInstance(new GsonConfiguration(configFile));
        bind(DatabaseConfiguration.class).toInstance(new GsonConfiguration(configFile));
    }

}
