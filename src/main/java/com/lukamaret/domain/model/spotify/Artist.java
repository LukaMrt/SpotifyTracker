package com.lukamaret.domain.model.spotify;

public class Artist {

    public final int id;
    public final String url;
    public final String uri;
    public final String name;

    public Artist(int id, String url, String uri, String name) {
        this.id = id;
        this.url = url;
        this.uri = uri;
        this.name = name;
    }

    public Artist(String url, String uri, String name) {
        this.id = -1;
        this.url = url;
        this.uri = uri;
        this.name = name;
    }

    public Artist setId(int id) {
        return new Artist(id, url, uri, name);
    }
}
