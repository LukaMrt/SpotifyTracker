package com.lukamaret.spotifytracker.infrastructure.spotify;

import com.lukamaret.spotifytracker.domain.application.spotify.ArtistsRepository;
import com.lukamaret.spotifytracker.domain.model.spotify.Artist;
import com.lukamaret.spotifytracker.infrastructure.database.DatabaseConnection;

import javax.inject.Inject;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

public class MariadbArtistsRepository implements ArtistsRepository {

    @Inject
    private DatabaseConnection connection;

    @Override
    public Artist save(Artist artist) {

        try {
            Connection connection = this.connection.getConnection();

            PreparedStatement statement = connection.prepareStatement("SELECT * FROM Artist WHERE name = ?");
            statement.setString(1, artist.name);
            ResultSet result = statement.executeQuery();

            if (!result.next()) {
                statement = connection.prepareStatement("INSERT INTO Artist (name, uri, url) VALUES (?, ?, ?)");
                statement.setString(1, artist.name);
                statement.setString(2, artist.uri);
                statement.setString(3, artist.url);
                statement.execute();
            }

            statement = connection.prepareStatement("SELECT * FROM Artist WHERE name = ?");
            statement.setString(1, artist.name);
            result = statement.executeQuery();
            result.next();

            artist = artist.setId(result.getInt("id"));
            connection.close();
        } catch (SQLException e) {
            e.printStackTrace();
        }

        return artist;
    }

}
