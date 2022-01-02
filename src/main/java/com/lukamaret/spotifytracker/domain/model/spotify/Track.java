package com.lukamaret.spotifytracker.domain.model.spotify;

import java.util.List;

public class Track {

    public final int id;
    public final String url;
    public final String uri;
    public final String name;
    public final List<Artist> artists;

    public Track(int id, String url, String uri, String name, List<Artist> artists) {
        this.id = id;
        this.url = url;
        this.uri = uri;
        this.name = name;
        this.artists = artists;
    }

    public Track(String url, String uri, String name, List<Artist> trackArtists) {
        this.artists = trackArtists;
        this.id = -1;
        this.url = url;
        this.uri = uri;
        this.name = name;
    }

    public Track setId(int id) {
        return new Track(id, url, uri, name, artists);
    }

    public Track setArtists(List<Artist> artists) {
        return new Track(id, url, uri, name, artists);
    }

}
