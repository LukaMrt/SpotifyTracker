package com.lukamaret.infrastructure.spotify;

import com.lukamaret.domain.application.spotify.PlaylistRepository;
import com.lukamaret.domain.model.spotify.Playlist;
import com.lukamaret.infrastructure.database.DatabaseConnection;

import javax.inject.Inject;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

public class PostgresPlaylistRepository implements PlaylistRepository {

    @Inject
    private DatabaseConnection connection;

    @Override
    public Playlist save(Playlist playlist) {

        try {
            Connection connection = this.connection.getConnection();

            PreparedStatement statement = connection.prepareStatement("SELECT * FROM playlist WHERE name = ?");
            statement.setString(1, playlist.name);
            ResultSet result = statement.executeQuery();

            if (!result.next()) {
                statement = connection.prepareStatement("INSERT INTO playlist (name, uri, url) VALUES (?, ?, ?)");
                statement.setString(1, playlist.name);
                statement.setString(2, playlist.uri);
                statement.setString(3, playlist.url);
                statement.execute();
            }

            statement = connection.prepareStatement("SELECT * FROM playlist WHERE name = ?");
            statement.setString(1, playlist.name);
            result = statement.executeQuery();
            result.next();

            playlist = playlist.setId(result.getInt("id"));

            connection.close();
        } catch (SQLException e) {
            e.printStackTrace();
        }

        return playlist;
    }

}
