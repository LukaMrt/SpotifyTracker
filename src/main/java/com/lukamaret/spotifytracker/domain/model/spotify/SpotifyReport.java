package com.lukamaret.spotifytracker.domain.model.spotify;

import java.util.List;

public class SpotifyReport {

    public final int listeningMinutes;
    public final int tracksCount;
    public final int artistsCount;
    public final int playlistsCount;
    public final List<Track> mostPlayedTracks;
    public final List<Artist> mostPlayedArtists;
    public final List<Playlist> mostPlayedPlaylists;

    public SpotifyReport(SpotifyReportBuilder builder) {
        this.listeningMinutes = builder.listeningMinutes;
        this.tracksCount = builder.tracksCount;
        this.artistsCount = builder.artistsCount;
        this.playlistsCount = builder.playlistsCount;
        this.mostPlayedTracks = builder.mostPlayedTracks;
        this.mostPlayedArtists = builder.mostPlayedArtists;
        this.mostPlayedPlaylists = builder.mostPlayedPlaylists;
    }

}
