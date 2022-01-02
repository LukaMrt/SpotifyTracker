package com.lukamaret.spotifytracker.domain.model.commands;

public interface Command {

    String getName();

    void execute(long authorId, long channelId, String[] args);

}
