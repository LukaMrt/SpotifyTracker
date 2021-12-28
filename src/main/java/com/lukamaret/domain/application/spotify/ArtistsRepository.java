package com.lukamaret.domain.application.spotify;

import com.lukamaret.domain.model.spotify.Artist;

public interface ArtistsRepository {

    Artist save(Artist artist);

}
