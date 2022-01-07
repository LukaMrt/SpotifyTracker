package com.lukamaret.spotifytracker.infrastructure.clean;

import com.lukamaret.spotifytracker.domain.application.clean.ChannelCleaner;
import org.javacord.api.DiscordApi;
import org.javacord.api.entity.message.Message;

import javax.inject.Inject;

public class DiscordChannelCleaner implements ChannelCleaner {

    @Inject
    private DiscordApi discord;

    @Override
    public void clean(String channelId, int messageCount) {

        discord.getTextChannelById(channelId)
                .ifPresent(channel -> channel.getMessages(messageCount)
                        .thenAccept(messages -> messages.forEach(Message::delete)));

    }

}
