package com.lukamaret.spotifytracker.domain.model.spotify.report;

import java.util.ArrayList;
import java.util.List;

public class SpotifyReportBuilder {

    public int listeningMinutes = 0;
    public int tracksCount = 0;
    public int artistsCount = 0;
    public int playlistsCount = 0;
    public List<ReportTrack> mostPlayedTracks = new ArrayList<>();
    public List<ReportArtist> mostPlayedArtists = new ArrayList<>();
    public List<ReportPlaylist> mostPlayedPlaylists = new ArrayList<>();

    private SpotifyReportBuilder() {
    }

    public static SpotifyReportBuilder aSpotifyReport() {
        return new SpotifyReportBuilder();
    }

    public SpotifyReportBuilder withListeningMinutes(int listeningMinutes) {
        this.listeningMinutes = listeningMinutes;
        return this;
    }

    public SpotifyReportBuilder withTracksCount(int tracksCount) {
        this.tracksCount = tracksCount;
        return this;
    }

    public SpotifyReportBuilder withArtistsCount(int artistsCount) {
        this.artistsCount = artistsCount;
        return this;
    }

    public SpotifyReportBuilder withPlaylistsCount(int playlistsCount) {
        this.playlistsCount = playlistsCount;
        return this;
    }

    public SpotifyReportBuilder withMostPlayedTracks(List<ReportTrack> mostPlayedTracks) {
        this.mostPlayedTracks = mostPlayedTracks;
        return this;
    }

    public SpotifyReportBuilder withMostPlayedArtists(List<ReportArtist> mostPlayedArtists) {
        this.mostPlayedArtists = mostPlayedArtists;
        return this;
    }

    public SpotifyReportBuilder withMostPlayedPlaylists(List<ReportPlaylist> mostPlayedPlaylist) {
        this.mostPlayedPlaylists = mostPlayedPlaylist;
        return this;
    }

    public SpotifyReportBuilder addMostPlayedTracks(ReportTrack track) {
        this.mostPlayedTracks.add(track);
        return this;
    }

    public SpotifyReportBuilder addMostPlayedArtists(ReportArtist artist) {
        this.mostPlayedArtists.add(artist);
        return this;
    }

    public SpotifyReportBuilder addMostPlayedTracksByArtist(ReportPlaylist playlist) {
        this.mostPlayedPlaylists.add(playlist);
        return this;
    }

    public SpotifyReport build() {
        return new SpotifyReport(this);
    }

}
