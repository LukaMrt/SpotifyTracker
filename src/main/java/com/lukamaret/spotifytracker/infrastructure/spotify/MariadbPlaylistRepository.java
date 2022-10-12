package com.lukamaret.spotifytracker.infrastructure.spotify;

import com.lukamaret.spotifytracker.domain.application.spotify.PlaylistRepository;
import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.infrastructure.database.DatabaseConnection;

import javax.inject.Inject;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

public class MariadbPlaylistRepository implements PlaylistRepository {

    @Inject
    private DatabaseConnection connection;

    @Override
    public Playlist save(Playlist playlist) {

        try {
            Connection connection = this.connection.getConnection();

            PreparedStatement statement = connection.prepareStatement("SELECT * FROM Playlist WHERE uri = ?");
            statement.setString(1, playlist.uri);
            ResultSet result = statement.executeQuery();

            if (!result.next()) {
                statement = connection.prepareStatement("INSERT INTO Playlist (name, uri, url) VALUES (?, ?, ?)");
                statement.setString(1, playlist.name);
                statement.setString(2, playlist.uri);
                statement.setString(3, playlist.url);
                statement.execute();
            }

            statement = connection.prepareStatement("SELECT * FROM Playlist WHERE uri = ?");
            statement.setString(1, playlist.uri);
            result = statement.executeQuery();
            result.next();

            playlist = playlist.setId(result.getInt("id"));

            if (result.getString("name") != null) {
                playlist = playlist.setName(result.getString("name"));
            }

            connection.close();
        } catch (SQLException e) {
            e.printStackTrace();
        }

        return playlist;
    }

}
