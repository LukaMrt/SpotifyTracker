package com.lukamaret.spotifytracker.domain.application.configuration;

import com.lukamaret.spotifytracker.domain.model.configuration.spotify.SpotifyCredentials;
import com.lukamaret.spotifytracker.domain.model.configuration.spotify.SpotifyTokens;

public interface SpotifyConfiguration {

    SpotifyCredentials getSpotifyCredentials();

    SpotifyTokens getSpotifyTokens();

    void save();

    void setAccessToken(String accessToken);

}
