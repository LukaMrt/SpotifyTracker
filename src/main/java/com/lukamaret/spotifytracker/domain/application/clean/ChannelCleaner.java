package com.lukamaret.spotifytracker.domain.application.clean;

public interface ChannelCleaner {

    void clean(String channelId, int messageCount);

}
