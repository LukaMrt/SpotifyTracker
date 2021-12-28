package com.lukamaret.ui.command;

import com.lukamaret.domain.model.commands.Command;
import org.javacord.api.DiscordApi;
import org.slf4j.LoggerFactory;

import javax.inject.Inject;

public class StopCommand implements Command {

    @Inject
    private DiscordApi api;

    @Override
    public String getName() {
        return "stop";
    }

    @Override
    public void execute(long authorId, long channelId, String[] args) {
        api.disconnect();
        LoggerFactory.getLogger(StopCommand.class).info("Spotify tracker stopped");
        System.exit(0);
    }

}
