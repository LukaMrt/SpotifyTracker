package com.lukamaret.spotifytracker.domain.model.spotify.report;

import com.lukamaret.spotifytracker.domain.model.spotify.Track;

public class ReportTrack {

    public final int timeMinutes;
    public final Track track;

    public ReportTrack(int timeMinutes, Track track) {
        this.timeMinutes = timeMinutes;
        this.track = track;
    }

}
