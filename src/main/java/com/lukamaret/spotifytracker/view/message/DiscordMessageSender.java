package com.lukamaret.spotifytracker.view.message;

import com.lukamaret.spotifytracker.domain.application.message.MessageSender;
import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.domain.model.spotify.SpotifyReport;
import com.lukamaret.spotifytracker.domain.model.spotify.Track;
import org.javacord.api.DiscordApi;
import org.javacord.api.entity.channel.TextChannel;
import org.javacord.api.entity.message.embed.EmbedBuilder;

import javax.inject.Inject;
import java.awt.*;
import java.util.Timer;
import java.util.TimerTask;

public class DiscordMessageSender implements MessageSender {

    @Inject
    private DiscordApi discord;

    @Override
    public void sendListening(long channelId, Track track, Playlist playlist) {

        StringBuilder artists = new StringBuilder();

        for (int i = 0; i < track.artists.size(); i++) {

            if (i == track.artists.size() - 1) {
                artists.append(track.artists.get(i).name);
                continue;
            }

            if (i == track.artists.size() - 2) {
                artists.append(track.artists.get(i).name).append(" et ");
                continue;
            }

            artists.append(track.artists.get(i).name).append(", ");
        }

        EmbedBuilder embedBuilder = new EmbedBuilder()
                .setTitle("Écoute en cours :")
                .addField("Titre", track.name, true)
                .addField("Artistes", artists.toString(), true)
                .addField("Playlist", playlist.name, true)
                .setAuthor(discord.getYourself().getName(), null, discord.getYourself().getAvatar())
                .setColor(Color.BLUE);

        discord.getTextChannelById(channelId).ifPresent(channel -> channel.sendMessage(embedBuilder));
    }

    @Override
    public void sendPrivateMessage(long userId, String message) {
        discord.getUserById(userId).thenAccept(user -> user.sendMessage(message));
    }

    @Override
    public void sendMessage(long channelId, String message) {
        discord.getTextChannelById(channelId).ifPresent(channel -> this.sendMessageToDelete(channel, message));
    }

    private void sendMessageToDelete(TextChannel channel, String message) {
        channel.sendMessage(message).thenAccept(message1 -> new Timer().schedule(new TimerTask() {
            @Override
            public void run() {
                message1.delete();
                cancel();
            }
        }, 2 * 60 * 1000));
    }

    @Override
    public void sendReport(String channelId, SpotifyReport report) {

        EmbedBuilder embedBuilder = new EmbedBuilder()
                .setTitle("Rapport Spotify")
                .addField("Temps d'écoute", formatMinutes(report.listeningMinutes), true)
                .addField("Nombre de titres", String.valueOf(report.tracksCount), false)
                .addField("Nombre de playlists", String.valueOf(report.playlistsCount), false)
                .addField("Nombre d'artistes", String.valueOf(report.artistsCount), false)
                .addField("Top titres", report.mostPlayedTracks.stream().map(track -> track.name).reduce((a, b) -> a + "\n" + b).orElse(""), false)
                .addField("Top artistes", report.mostPlayedArtists.stream().map(artist -> artist.name).reduce((a, b) -> a + "\n" + b).orElse(""), false)
                .addField("Top playlists", report.mostPlayedPlaylists.stream().map(playlist -> playlist.name).reduce((a, b) -> a + "\n" + b).orElse(""), false)
                .setAuthor(discord.getYourself().getName(), null, discord.getYourself().getAvatar())
                .setColor(Color.BLUE);

        discord.getTextChannelById(Long.parseLong(channelId)).ifPresent(channel -> channel.sendMessage(embedBuilder));
    }

    private String formatMinutes(int listeningMinutes) {

        int days = listeningMinutes / (24 * 60);
        int hours = listeningMinutes / 60;
        int minutes = listeningMinutes % 60;

        return days + " jours, " + hours + " heures et " + minutes + " minutes";
    }

}
