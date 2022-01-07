package com.lukamaret.spotifytracker.infrastructure.listeners;

import com.google.inject.Injector;
import com.lukamaret.spotifytracker.domain.application.listeners.ListenersRepository;
import com.lukamaret.spotifytracker.ui.listener.CommandListener;
import org.javacord.api.DiscordApi;

public class DiscordListenersRepository implements ListenersRepository {

    private final Injector injector;

    public DiscordListenersRepository(Injector injector) {
        this.injector = injector;
    }

    @Override
    public void registerListeners() {

        DiscordApi api = injector.getInstance(DiscordApi.class);
        api.addListener(new CommandListener(injector));

    }

}
