package com.lukamaret.spotifytracker.domain.application.spotify;

import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.domain.model.spotify.Track;
import com.lukamaret.spotifytracker.domain.model.spotify.report.ReportArtist;
import com.lukamaret.spotifytracker.domain.model.spotify.report.ReportPlaylist;
import com.lukamaret.spotifytracker.domain.model.spotify.report.ReportTrack;

import java.time.LocalDate;
import java.util.List;

public interface ListeningRepository {

    void save(Track track, Playlist playlist);

    int getListeningMinutes(LocalDate start, LocalDate end);

    int getTracksCount(LocalDate start, LocalDate end);

    int getArtistsCount(LocalDate start, LocalDate end);

    int getPlaylistsCount(LocalDate start, LocalDate end);

    List<ReportTrack> getMostPlayedTracks(LocalDate start, LocalDate end);

    List<ReportArtist> getMostPlayedArtists(LocalDate start, LocalDate end);

    List<ReportPlaylist> getMostPlayedPlaylists(LocalDate start, LocalDate end);

}
