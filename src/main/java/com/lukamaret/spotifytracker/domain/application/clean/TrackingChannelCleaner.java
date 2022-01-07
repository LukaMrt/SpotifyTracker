package com.lukamaret.spotifytracker.domain.application.clean;

import com.lukamaret.spotifytracker.domain.application.configuration.DiscordConfiguration;

import javax.inject.Inject;

public class TrackingChannelCleaner {

    @Inject
    private DiscordConfiguration discordConfiguration;

    @Inject
    private ChannelCleaner channelCleaner;

    public void cleanTrackingChannel() {

        String trackingChannel = discordConfiguration.getTrackingChannel();
        channelCleaner.clean(trackingChannel, 24 * 60 * 2 + 100);

    }

}
