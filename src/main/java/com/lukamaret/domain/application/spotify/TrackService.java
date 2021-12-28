package com.lukamaret.domain.application.spotify;

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

    public void registerListening(Track track, Playlist playlist) {
        playlist = playlistRepository.save(playlist);
        List<Artist> artists = track.artists.stream()
                .map(artist -> artistsRepository.save(artist))
                .toList();
        track = track.setArtists(artists);
        track = trackRepository.save(track);
        listeningRepository.save(track, playlist);
    }

}
