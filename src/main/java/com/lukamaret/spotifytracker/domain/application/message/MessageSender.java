package com.lukamaret.spotifytracker.domain.application.message;

import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.domain.model.spotify.report.SpotifyReport;
import com.lukamaret.spotifytracker.domain.model.spotify.Track;

public interface MessageSender {

    void sendListening(String channelId, Track track, Playlist playlist);

    void sendMessage(String channelId, String message);

    void sendReport(String channelId, SpotifyReport report, String title);

}
