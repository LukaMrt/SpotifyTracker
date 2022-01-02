package com.lukamaret.spotifytracker.domain.application.spotify;

import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;

public interface PlaylistRepository {

    Playlist save(Playlist playlist);

}
