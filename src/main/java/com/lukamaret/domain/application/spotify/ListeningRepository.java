package com.lukamaret.domain.application.spotify;

import com.lukamaret.domain.model.spotify.Playlist;
import com.lukamaret.domain.model.spotify.Track;

public interface ListeningRepository {

    void save(Track track, Playlist playlist);

}
