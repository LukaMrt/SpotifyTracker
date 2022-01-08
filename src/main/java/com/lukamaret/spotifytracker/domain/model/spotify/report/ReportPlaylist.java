package com.lukamaret.spotifytracker.domain.model.spotify.report;

import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;

public class ReportPlaylist {

    public final int timeMinutes;
    public final Playlist playlist;

    public ReportPlaylist(int timeMinutes, Playlist playlist) {
        this.timeMinutes = timeMinutes;
        this.playlist = playlist;
    }

}
