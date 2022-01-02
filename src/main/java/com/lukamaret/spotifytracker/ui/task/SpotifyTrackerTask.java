package com.lukamaret.spotifytracker.ui.task;

import com.lukamaret.spotifytracker.domain.application.configuration.SpotifyConfiguration;
import com.lukamaret.spotifytracker.domain.application.spotify.TrackService;
import com.lukamaret.spotifytracker.domain.model.spotify.Artist;
import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.domain.model.spotify.Track;
import com.wrapper.spotify.SpotifyApi;
import com.wrapper.spotify.model_objects.credentials.AuthorizationCodeCredentials;
import com.wrapper.spotify.model_objects.miscellaneous.CurrentlyPlaying;
import com.wrapper.spotify.model_objects.specification.ArtistSimplified;
import com.wrapper.spotify.model_objects.specification.PlaylistSimplified;

import javax.inject.Inject;
import java.util.Arrays;
import java.util.List;
import java.util.TimerTask;

public class SpotifyTrackerTask extends TimerTask {

    @Inject
    private SpotifyApi spotifyApi;

    @Inject
    private SpotifyConfiguration spotifyConfiguration;

    @Inject
    private TrackService trackService;

    @Override
    public void run() {

        try {

            refreshToken();

            CurrentlyPlaying currentTrack = spotifyApi.getUsersCurrentlyPlayingTrack()
                    .build()
                    .execute();

            if (currentTrack == null || !currentTrack.getIs_playing()) {
                trackService.noListening();
                return;
            }

            Track track = buildTrack(currentTrack);
            Playlist playlist = buildPlaylist(currentTrack);
            trackService.registerListening(track, playlist);

        } catch (Exception e) {
            e.printStackTrace();
        }

    }

    private void refreshToken() throws Exception {
        AuthorizationCodeCredentials refreshAccess = spotifyApi.authorizationCodeRefresh().build().execute();
        spotifyApi.setAccessToken(refreshAccess.getAccessToken());
        spotifyConfiguration.setAccessToken(refreshAccess.getAccessToken());
        spotifyConfiguration.save();
    }

    private Track buildTrack(CurrentlyPlaying currentTrack) throws Exception {
        ArtistSimplified[] artists = spotifyApi.getTrack(currentTrack.getItem().getId())
                .build()
                .execute()
                .getArtists();

        List<Artist> trackArtists = Arrays.stream(artists)
                .map(artist -> new Artist(
                        artist.getExternalUrls().get("spotify"),
                        artist.getUri(),
                        artist.getName()
                )).toList();

        return new Track(
                currentTrack.getItem().getExternalUrls().get("spotify"),
                currentTrack.getItem().getUri(),
                currentTrack.getItem().getName(),
                trackArtists);
    }

    private Playlist buildPlaylist(CurrentlyPlaying currentTrack) throws Exception {
        String playlistUri = currentTrack.getContext().getUri();

        String playlistName = Arrays.stream(spotifyApi.getListOfCurrentUsersPlaylists()
                        .build()
                        .execute()
                        .getItems())
                .filter(playlist -> playlist.getUri().equals(playlistUri))
                .findFirst()
                .map(PlaylistSimplified::getName)
                .orElse("Unknown");

        return new Playlist(
                currentTrack.getContext().getExternalUrls().get("spotify"),
                playlistUri,
                playlistName
        );
    }

}
