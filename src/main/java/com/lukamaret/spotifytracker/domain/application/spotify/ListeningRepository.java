package com.lukamaret.spotifytracker.domain.application.spotify;

import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.domain.model.spotify.Track;

public interface ListeningRepository {

    void save(Track track, Playlist playlist);

}
