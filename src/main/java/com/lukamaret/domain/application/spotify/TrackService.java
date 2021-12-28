package com.lukamaret.domain.application.spotify;

import com.lukamaret.domain.application.configuration.DiscordConfiguration;
import com.lukamaret.domain.application.message.MessageSender;
import com.lukamaret.domain.model.spotify.Artist;
import com.lukamaret.domain.model.spotify.Playlist;
import com.lukamaret.domain.model.spotify.Track;

import javax.inject.Inject;
import java.util.List;

public class TrackService {

    @Inject
    private TrackRepository trackRepository;

    @Inject
    private PlaylistRepository playlistRepository;

    @Inject
    private ListeningRepository listeningRepository;

    @Inject
    private ArtistsRepository artistsRepository;

    @Inject
    private MessageSender messageSender;

    @Inject
    private DiscordConfiguration discordConfiguration;

    public void registerListening(Track track, Playlist playlist) {
        playlist = playlistRepository.save(playlist);
        List<Artist> artists = track.artists.stream()
                .map(artist -> artistsRepository.save(artist))
                .toList();
        track = track.setArtists(artists);
        track = trackRepository.save(track);
        listeningRepository.save(track, playlist);

        messageSender.sendListening(Long.parseLong(discordConfiguration.getLogsChannel()), track, playlist);
    }

}
