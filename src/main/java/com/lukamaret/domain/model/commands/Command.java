package com.lukamaret.domain.model.commands;

public interface Command {

    String getName();

    void execute(long authorId, long channelId, String[] args);

}
