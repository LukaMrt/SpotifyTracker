package com.lukamaret.spotifytracker.main;

import com.google.inject.Guice;
import com.google.inject.Injector;
import com.lukamaret.spotifytracker.infrastructure.listeners.DiscordListenersRepository;
import com.lukamaret.spotifytracker.infrastructure.tasks.DefaultTasksRepository;
import com.lukamaret.spotifytracker.main.guice.ConfigurationModule;
import com.lukamaret.spotifytracker.main.guice.MainModule;
import org.slf4j.LoggerFactory;

import java.io.File;

public class Main {

    public static void main(String[] args) {

        File configFile = new File("configuration.json");
        Injector configurationInjector = Guice.createInjector(new ConfigurationModule(configFile));
        Injector injector = Guice.createInjector(new MainModule(configurationInjector));

        new DiscordListenersRepository(injector).registerListeners();
        new DefaultTasksRepository(injector).registerTasks();

        LoggerFactory.getLogger(Main.class).info("Spotify tracker started");
    }

}
