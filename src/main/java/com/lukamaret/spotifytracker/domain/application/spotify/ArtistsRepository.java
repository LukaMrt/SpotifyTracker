package com.lukamaret.spotifytracker.domain.application.spotify;

import com.lukamaret.spotifytracker.domain.model.spotify.Artist;

public interface ArtistsRepository {

    Artist save(Artist artist);

}
