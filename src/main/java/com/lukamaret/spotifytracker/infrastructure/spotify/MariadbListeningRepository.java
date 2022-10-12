package com.lukamaret.spotifytracker.infrastructure.spotify;

import com.lukamaret.spotifytracker.domain.application.spotify.ListeningRepository;
import com.lukamaret.spotifytracker.domain.model.spotify.Artist;
import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.domain.model.spotify.Track;
import com.lukamaret.spotifytracker.domain.model.spotify.report.ReportArtist;
import com.lukamaret.spotifytracker.domain.model.spotify.report.ReportPlaylist;
import com.lukamaret.spotifytracker.domain.model.spotify.report.ReportTrack;
import com.lukamaret.spotifytracker.infrastructure.database.DatabaseConnection;

import javax.inject.Inject;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.time.LocalDate;
import java.util.ArrayList;
import java.util.List;

public class MariadbListeningRepository implements ListeningRepository {

    @Inject
    private DatabaseConnection connection;

    @Override
    public void save(Track track, Playlist playlist) {

        try {
            Connection connection = this.connection.getConnection();

            PreparedStatement statement = connection.prepareStatement("INSERT INTO Listening (id_track, id_playlist) VALUES (?, ?)");
            statement.setInt(1, track.id);
            statement.setInt(2, playlist.id);
            statement.execute();

            connection.close();
        } catch (SQLException e) {
            e.printStackTrace();
        }

    }

    @Override
    public int getListeningMinutes(LocalDate start, LocalDate end) {

        int minutes;

        try {

            Connection connection = this.connection.getConnection();

            String sql = "SELECT COUNT(*) / 2 FROM Listening WHERE ? <= date AND date < ?";
            PreparedStatement statement = connection.prepareStatement(sql);
            statement.setTimestamp(1, java.sql.Timestamp.valueOf(start.atStartOfDay()));
            statement.setTimestamp(2, java.sql.Timestamp.valueOf(end.atStartOfDay()));

            ResultSet resultSet = statement.executeQuery();
            resultSet.next();

            minutes = resultSet.getInt(1);
            connection.close();

        } catch (SQLException e) {
            e.printStackTrace();
            minutes = 0;
        }

        return minutes;
    }

    @Override
    public int getTracksCount(LocalDate start, LocalDate end) {

        int tracksCount;

        try {

            Connection connection = this.connection.getConnection();

            String sql = "SELECT COUNT(DISTINCT id_track) FROM Listening WHERE ? <= date AND date < ?";
            PreparedStatement statement = connection.prepareStatement(sql);
            statement.setTimestamp(1, java.sql.Timestamp.valueOf(start.atStartOfDay()));
            statement.setTimestamp(2, java.sql.Timestamp.valueOf(end.atStartOfDay()));

            ResultSet result = statement.executeQuery();
            result.next();

            tracksCount = result.getInt(1);
            connection.close();

        } catch (SQLException e) {
            e.printStackTrace();
            tracksCount = 0;
        }

        return tracksCount;
    }

    @Override
    public int getArtistsCount(LocalDate start, LocalDate end) {

        int artistsCount;

        try {

            Connection connection = this.connection.getConnection();

            String sql = "SELECT COUNT(DISTINCT id_artist) FROM Listening JOIN Track ON Listening.id_track = Track.id JOIN Author ON Track.id = Author.id_track WHERE ? <= date AND date < ?";
            PreparedStatement statement = connection.prepareStatement(sql);
            statement.setTimestamp(1, java.sql.Timestamp.valueOf(start.atStartOfDay()));
            statement.setTimestamp(2, java.sql.Timestamp.valueOf(end.atStartOfDay()));

            ResultSet result = statement.executeQuery();
            result.next();

            artistsCount = result.getInt(1);
            connection.close();

        } catch (SQLException e) {
            e.printStackTrace();
            artistsCount = 0;
        }

        return artistsCount;
    }

    @Override
    public int getPlaylistsCount(LocalDate start, LocalDate end) {

        int playlistsCount;

        try {

            Connection connection = this.connection.getConnection();

            String sql = "SELECT COUNT(DISTINCT id_playlist) FROM Listening WHERE ? <= date AND date < ?";
            PreparedStatement statement = connection.prepareStatement(sql);
            statement.setTimestamp(1, java.sql.Timestamp.valueOf(start.atStartOfDay()));
            statement.setTimestamp(2, java.sql.Timestamp.valueOf(end.atStartOfDay()));

            ResultSet result = statement.executeQuery();
            result.next();

            playlistsCount = result.getInt(1);
            connection.close();

        } catch (SQLException e) {
            e.printStackTrace();
            playlistsCount = 0;
        }

        return playlistsCount;
    }

    @Override
    public List<ReportTrack> getMostPlayedTracks(LocalDate start, LocalDate end) {

        List<ReportTrack> tracks = new ArrayList<>();

        try {

            Connection connection = this.connection.getConnection();

            String sql = "SELECT Track.id, uri, url, Track.name, COUNT(*) / 2 AS time FROM Listening JOIN Track ON Listening.id_track = Track.id WHERE ? <= date AND date < ? GROUP BY Track.id, uri, url, Track.name ORDER BY COUNT(*) DESC LIMIT 5";
            PreparedStatement statement = connection.prepareStatement(sql);
            statement.setTimestamp(1, java.sql.Timestamp.valueOf(start.atStartOfDay()));
            statement.setTimestamp(2, java.sql.Timestamp.valueOf(end.atStartOfDay()));
            ResultSet resultSet = statement.executeQuery();

            while (resultSet.next()) {
                Track track = new Track(
                        resultSet.getInt("id"),
                        resultSet.getString("url"),
                        resultSet.getString("uri"),
                        resultSet.getString("name"),
                        new ArrayList<>()
                );
                tracks.add(new ReportTrack(resultSet.getInt("time"), track));
            }

            connection.close();

        } catch (SQLException e) {
            e.printStackTrace();
        }

        return tracks;
    }

    @Override
    public List<ReportArtist> getMostPlayedArtists(LocalDate start, LocalDate end) {

        List<ReportArtist> artists = new ArrayList<>();

        try {

            Connection connection = this.connection.getConnection();

            String sql = "SELECT Artist.id, Artist.uri, Artist.url, Artist.name, COUNT(*) / 2 AS time FROM Listening JOIN Track ON Listening.id_track = Track.id JOIN Author ON Track.id = Author.id_track JOIN Artist ON Author.id_artist = Artist.id WHERE ? <= date AND date < ? GROUP BY Artist.id, Artist.uri, Artist.url, Artist.name ORDER BY COUNT(*) DESC LIMIT 5";
            PreparedStatement statement = connection.prepareStatement(sql);
            statement.setTimestamp(1, java.sql.Timestamp.valueOf(start.atStartOfDay()));
            statement.setTimestamp(2, java.sql.Timestamp.valueOf(end.atStartOfDay()));
            ResultSet resultSet = statement.executeQuery();

            while (resultSet.next()) {
                Artist artist = new Artist(
                        resultSet.getInt("id"),
                        resultSet.getString("url"),
                        resultSet.getString("uri"),
                        resultSet.getString("name")
                );
                artists.add(new ReportArtist(resultSet.getInt("time"), artist));
            }

            connection.close();

        } catch (SQLException e) {
            e.printStackTrace();
        }

        return artists;
    }

    @Override
    public List<ReportPlaylist> getMostPlayedPlaylists(LocalDate start, LocalDate end) {

        List<ReportPlaylist> playlists = new ArrayList<>();

        try {

            Connection connection = this.connection.getConnection();

            String sql = "SELECT id, uri, url, Playlist.name, COUNT(*) / 2 AS time FROM Listening JOIN Playlist ON Listening.id_playlist = Playlist.id WHERE ? <= date AND date < ? GROUP BY id, uri, url, Playlist.name ORDER BY COUNT(*) DESC LIMIT 5";
            PreparedStatement statement = connection.prepareStatement(sql);
            statement.setTimestamp(1, java.sql.Timestamp.valueOf(start.atStartOfDay()));
            statement.setTimestamp(2, java.sql.Timestamp.valueOf(end.atStartOfDay()));
            ResultSet resultSet = statement.executeQuery();

            while (resultSet.next()) {
                Playlist playlist = new Playlist(
                        resultSet.getInt("id"),
                        resultSet.getString("url"),
                        resultSet.getString("uri"),
                        resultSet.getString("name")
                );
                playlists.add(new ReportPlaylist(resultSet.getInt("time"), playlist));
            }

            connection.close();

        } catch (SQLException e) {
            e.printStackTrace();
        }

        return playlists;
    }

}
