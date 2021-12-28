package com.lukamaret.domain.model.spotify;

public class Playlist {

    public final int id;
    public final String url;
    public final String uri;
    public final String name;

    public Playlist(int id, String url, String uri, String name) {
        this.id = id;
        this.url = url;
        this.uri = uri;
        this.name = name;
    }

    public Playlist(String url, String uri, String name) {
        this.id = -1;
        this.url = url;
        this.uri = uri;
        this.name = name;
    }

    public Playlist setId(int id) {
        return new Playlist(id, url, uri, name);
    }

}
