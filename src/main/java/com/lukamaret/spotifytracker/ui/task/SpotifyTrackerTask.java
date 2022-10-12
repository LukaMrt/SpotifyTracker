package com.lukamaret.spotifytracker.ui.task;

import com.lukamaret.spotifytracker.domain.application.configuration.SpotifyConfiguration;
import com.lukamaret.spotifytracker.domain.application.spotify.TrackService;
import com.lukamaret.spotifytracker.domain.model.spotify.Artist;
import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.domain.model.spotify.Track;
import se.michaelthelin.spotify.SpotifyApi;
import se.michaelthelin.spotify.model_objects.IPlaylistItem;
import se.michaelthelin.spotify.model_objects.credentials.AuthorizationCodeCredentials;
import se.michaelthelin.spotify.model_objects.miscellaneous.CurrentlyPlaying;
import se.michaelthelin.spotify.model_objects.specification.ArtistSimplified;
import se.michaelthelin.spotify.model_objects.specification.PlaylistSimplified;

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

            if (currentTrack == null || currentTrack.getItem() == null || !currentTrack.getIs_playing() || spotifyApi.getTrack(currentTrack.getItem().getId()) == null) {
                trackService.noListening();
                return;
            }

            trackService.registerListening(buildTrack(currentTrack), buildPlaylist(currentTrack));

        } catch (Exception e) {
            e.printStackTrace();
            trackService.noListening();
        }

    }

    private void refreshToken() throws Exception {
        AuthorizationCodeCredentials refreshAccess = spotifyApi.authorizationCodeRefresh().build().execute();
        spotifyApi.setAccessToken(refreshAccess.getAccessToken());
        spotifyConfiguration.setAccessToken(refreshAccess.getAccessToken());
        spotifyConfiguration.save();
    }

    private Track buildTrack(CurrentlyPlaying currentTrack) throws Exception {
        IPlaylistItem item = currentTrack.getItem();
        ArtistSimplified[] artists = spotifyApi.getTrack(item.getId())
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
                item.getExternalUrls().get("spotify"),
                item.getUri(),
                item.getName(),
                trackArtists
        );
    }

    private Playlist buildPlaylist(CurrentlyPlaying currentTrack) throws Exception {

        if (currentTrack.getContext() == null) {
            return new Playlist("free", "free", "Free");
        }

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
