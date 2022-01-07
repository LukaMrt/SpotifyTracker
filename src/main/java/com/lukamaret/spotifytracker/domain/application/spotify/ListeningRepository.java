package com.lukamaret.spotifytracker.domain.application.spotify;

import com.lukamaret.spotifytracker.domain.model.spotify.Artist;
import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.domain.model.spotify.Track;

import java.time.LocalDate;
import java.util.List;

public interface ListeningRepository {

    void save(Track track, Playlist playlist);

    int getListeningMinutes(LocalDate start, LocalDate end);

    int getTracksCount(LocalDate start, LocalDate end);

    int getArtistsCount(LocalDate start, LocalDate end);

    int getPlaylistsCount(LocalDate start, LocalDate end);

    List<Track> getMostPlayedTracks(LocalDate start, LocalDate end);

    List<Artist> getMostPlayedArtists(LocalDate start, LocalDate end);

    List<Playlist> getMostPlayedPlaylists(LocalDate start, LocalDate end);

}
