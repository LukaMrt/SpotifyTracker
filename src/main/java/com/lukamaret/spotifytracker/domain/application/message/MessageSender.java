package com.lukamaret.spotifytracker.domain.application.message;

import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.domain.model.spotify.Track;

public interface MessageSender {

    void sendListening(long channelId, Track track, Playlist playlist);

    void sendPrivateMessage(long userId, String message);

    void sendMessage(long channelId, String message);

}
