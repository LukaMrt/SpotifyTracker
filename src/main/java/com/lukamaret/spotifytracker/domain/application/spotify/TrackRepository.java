package com.lukamaret.spotifytracker.domain.application.spotify;

import com.lukamaret.spotifytracker.domain.model.spotify.Track;

public interface TrackRepository {

    Track save(Track track);

}
