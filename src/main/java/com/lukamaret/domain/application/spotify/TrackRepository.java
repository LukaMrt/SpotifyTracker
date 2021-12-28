package com.lukamaret.domain.application.spotify;

import com.lukamaret.domain.model.spotify.Track;

public interface TrackRepository {

    Track save(Track track);

}
