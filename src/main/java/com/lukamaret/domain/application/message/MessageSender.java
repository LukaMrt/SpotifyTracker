package com.lukamaret.domain.application.message;

import com.lukamaret.domain.model.spotify.Playlist;
import com.lukamaret.domain.model.spotify.Track;

public interface MessageSender {

    void sendListening(long channelId, Track track, Playlist playlist);

}
