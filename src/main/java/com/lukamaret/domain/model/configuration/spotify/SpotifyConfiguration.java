package com.lukamaret.domain.model.configuration.spotify;

public class SpotifyConfiguration {

    private SpotifyCredentials credentials = new SpotifyCredentials();
    private SpotifyTokens tokens = new SpotifyTokens();

    public SpotifyCredentials getCredentials() {
        return credentials;
    }

    public SpotifyTokens getTokens() {
        return tokens;
    }

}
