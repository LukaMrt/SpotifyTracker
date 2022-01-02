package com.lukamaret.spotifytracker.ui.command;

import com.lukamaret.spotifytracker.domain.model.commands.Command;
import org.javacord.api.DiscordApi;

import javax.inject.Inject;

public class PingCommand implements Command {

    @Inject
    private DiscordApi api;

    @Override
    public String getName() {
        return "ping";
    }

    @Override
    public void execute(long authorId, long channelId, String[] args) {
        api.getTextChannelById(channelId).ifPresent(channel -> channel.sendMessage("Pong!"));
    }

}
