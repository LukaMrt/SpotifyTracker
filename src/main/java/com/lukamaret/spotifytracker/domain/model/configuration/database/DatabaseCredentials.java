package com.lukamaret.spotifytracker.domain.model.configuration.database;

public class DatabaseCredentials {

    private String host = "localhost";
    private int port = 3306;
    private String user = "user";
    private String password = "password";
    private String database = "database";

    public String getHost() {
        return host;
    }

    public int getPort() {
        return port;
    }

    public String getUser() {
        return user;
    }

    public String getPassword() {
        return password;
    }

    public String getDatabase() {
        return database;
    }

}
