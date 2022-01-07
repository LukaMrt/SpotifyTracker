package com.lukamaret.spotifytracker.ui.task;

import com.lukamaret.spotifytracker.domain.application.clean.TrackingChannelCleaner;

import javax.inject.Inject;
import java.util.TimerTask;

public class DailyCleanerTask extends TimerTask {

    @Inject
    private TrackingChannelCleaner trackingChannelCleaner;

    @Override
    public void run() {
        trackingChannelCleaner.cleanTrackingChannel();
    }

}
