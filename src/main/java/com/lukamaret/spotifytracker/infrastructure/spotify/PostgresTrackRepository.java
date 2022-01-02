package com.lukamaret.spotifytracker.infrastructure.spotify;

import com.lukamaret.spotifytracker.domain.application.spotify.TrackRepository;
import com.lukamaret.spotifytracker.domain.model.spotify.Artist;
import com.lukamaret.spotifytracker.domain.model.spotify.Track;
import com.lukamaret.spotifytracker.infrastructure.database.DatabaseConnection;

import javax.inject.Inject;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;

public class PostgresTrackRepository implements TrackRepository {

    @Inject
    private DatabaseConnection connection;

    @Override
    public Track save(Track track) {

        try {
            Connection connection = this.connection.getConnection();

            PreparedStatement statement = connection.prepareStatement("SELECT * FROM track WHERE name = ?");
            statement.setString(1, track.name);
            ResultSet result = statement.executeQuery();

            if (!result.next()) {
                statement = connection.prepareStatement("INSERT INTO track (name, uri, url) VALUES (?, ?, ?)");
                statement.setString(1, track.name);
                statement.setString(2, track.uri);
                statement.setString(3, track.url);
                statement.execute();
            }



            statement = connection.prepareStatement("SELECT * FROM track WHERE name = ?");
            statement.setString(1, track.name);
            result = statement.executeQuery();
            result.next();

            track = track.setId(result.getInt("id"));
            saveArtists(track, connection);

            connection.close();
        } catch (SQLException e) {
            e.printStackTrace();
        }

        return track;
    }

    private void saveArtists(Track track, Connection connection) throws SQLException {

        for (Artist artist : track.artists) {

            PreparedStatement statement = connection.prepareStatement("SELECT * FROM author WHERE id_track = ? AND id_artist = ?");
            statement.setInt(1, track.id);
            statement.setInt(2, artist.id);

            if (!statement.executeQuery().next()) {
                statement = connection.prepareStatement("INSERT INTO author (id_track, id_artist) VALUES (?, ?)");
                statement.setInt(1, track.id);
                statement.setInt(2, artist.id);
                statement.execute();
            }

        }

    }

}
