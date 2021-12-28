package com.lukamaret.domain.model.configuration.spotify;

public class SpotifyTokens {

    private String accessToken = "accessToken";
    private String refreshToken = "refreshToken";

    public String getAccessToken() {
        return accessToken;
    }

    public String getRefreshToken() {
        return refreshToken;
    }

    public void setAccessToken(String accessToken) {
        this.accessToken = accessToken;
    }

}
