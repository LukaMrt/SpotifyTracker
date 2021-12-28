package com.lukamaret.domain.application.spotify;

import com.lukamaret.domain.model.spotify.Playlist;

public interface PlaylistRepository {

    Playlist save(Playlist playlist);

}
