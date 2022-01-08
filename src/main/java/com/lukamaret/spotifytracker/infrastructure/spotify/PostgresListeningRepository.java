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

    @Override
    public int getListeningMinutes(LocalDate start, LocalDate end) {

        int minutes;

        try {

            Connection connection = this.connection.getConnection();

            String sql = "SELECT COUNT(*) / 2 FROM listening WHERE ? <= date AND date < ?";
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

            String sql = "SELECT COUNT(DISTINCT id_track) FROM listening WHERE ? <= date AND date < ?";
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

            String sql = "SELECT COUNT(DISTINCT id_artist) FROM listening JOIN track ON listening.id_track = track.id JOIN author ON track.id = author.id_track WHERE ? <= date AND date < ?";
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

            String sql = "SELECT COUNT(DISTINCT id_playlist) FROM listening WHERE ? <= date AND date < ?";
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

            String sql = "SELECT track.id, uri, url, track.name, COUNT(*) / 2 AS time FROM listening JOIN track ON listening.id_track = track.id WHERE ? <= date AND date < ? GROUP BY track.id, uri, url, track.name ORDER BY COUNT(*) DESC LIMIT 5";
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

            String sql = "SELECT artist.id, artist.uri, artist.url, artist.name, COUNT(*) / 2 AS time FROM listening JOIN track ON listening.id_track = track.id JOIN author ON track.id = author.id_track JOIN artist ON author.id_artist = artist.id WHERE ? <= date AND date < ? GROUP BY artist.id, artist.uri, artist.url, artist.name ORDER BY COUNT(*) DESC LIMIT 5";
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

            String sql = "SELECT id, uri, url, playlist.name, COUNT(*) / 2 AS time FROM listening JOIN playlist ON listening.id_playlist = playlist.id WHERE ? <= date AND date < ? GROUP BY id, uri, url, playlist.name ORDER BY COUNT(*) DESC LIMIT 5";
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
