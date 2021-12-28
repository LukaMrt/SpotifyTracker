package com.lukamaret.infrastructure.spotify;

import com.lukamaret.domain.application.spotify.ListeningRepository;
import com.lukamaret.domain.model.spotify.Playlist;
import com.lukamaret.domain.model.spotify.Track;
import com.lukamaret.infrastructure.database.DatabaseConnection;

import javax.inject.Inject;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.SQLException;

public class PostgresListeningRepository implements ListeningRepository {

    @Inject
    private DatabaseConnection connection;

    @Override
    public void save(Track track, Playlist playlist) {

        try {
            Connection connection = this.connection.getConnection();

            PreparedStatement statement = connection.prepareStatement("INSERT INTO listening (id_track, id_playlist) VALUES (?, ?)");
            statement.setInt(1, track.id);
            statement.setInt(2, playlist.id);
            statement.execute();

            connection.close();
        } catch (SQLException e) {
            e.printStackTrace();
        }

    }

}
