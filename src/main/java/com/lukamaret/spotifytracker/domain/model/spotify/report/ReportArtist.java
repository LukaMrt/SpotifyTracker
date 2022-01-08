package com.lukamaret.spotifytracker.domain.model.spotify.report;

import com.lukamaret.spotifytracker.domain.model.spotify.Artist;

public class ReportArtist {

    public final int timeMinutes;
    public final Artist artist;

    public ReportArtist(int timeMinutes, Artist artist) {
        this.timeMinutes = timeMinutes;
        this.artist = artist;
    }

}
