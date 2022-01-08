package com.lukamaret.spotifytracker.view.message;

import com.lukamaret.spotifytracker.domain.application.message.MessageSender;
import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.domain.model.spotify.report.SpotifyReport;
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

        discord.getTextChannelById(channelId).ifPresent(channel -> sendEmbedToDelete(channel, embedBuilder));
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

    private void sendEmbedToDelete(TextChannel channel, EmbedBuilder embed) {
        channel.sendMessage(embed).thenAccept(message1 -> new Timer().schedule(new TimerTask() {
            @Override
            public void run() {
                message1.delete();
                cancel();
            }
        }, 2 * 60 * 1000));
    }

    @Override
    public void sendReport(String channelId, SpotifyReport report, String title) {

        String tracks = report.mostPlayedTracks.stream()
                .map(track -> "__" + track.track.name + " :__ " + formatMinutes(track.timeMinutes))
                .reduce((a, b) -> a + "\n" + b)
                .orElse("");

        String artists = report.mostPlayedArtists.stream()
                .map(artist -> "__" + artist.artist.name + " :__ " + formatMinutes(artist.timeMinutes))
                .reduce((a, b) -> a + "\n" + b)
                .orElse("");

        String playlists = report.mostPlayedPlaylists.stream()
                .map(playlist -> "__" + playlist.playlist.name + " :__ " + formatMinutes(playlist.timeMinutes))
                .reduce((a, b) -> a + "\n" + b)
                .orElse("");

        EmbedBuilder embedBuilder = new EmbedBuilder()
                .setTitle(title)
                .addField("Temps d'écoute", formatMinutes(report.listeningMinutes), true)
                .addField("Nombre de titres", String.valueOf(report.tracksCount), false)
                .addField("Nombre de playlists", String.valueOf(report.playlistsCount), false)
                .addField("Nombre d'artistes", String.valueOf(report.artistsCount), false)
                .addField("Top titres", tracks, false)
                .addField("Top artistes", artists, false)
                .addField("Top playlists", playlists, false)
                .setAuthor(discord.getYourself().getName(), null, discord.getYourself().getAvatar())
                .setColor(Color.BLUE);

        discord.getTextChannelById(Long.parseLong(channelId)).ifPresent(channel -> channel.sendMessage(embedBuilder));
    }

    private String formatMinutes(int listeningMinutes) {

        int days = listeningMinutes / (24 * 60);
        int hours = listeningMinutes / 60;
        int minutes = listeningMinutes % 60;

        StringBuilder sb = new StringBuilder();

        if (days > 0) {
            sb.append(days)
                    .append(" jour")
                    .append(days > 1 ? "s" : "");
        }

        if (hours > 0) {
            sb.append(days > 0 ? ", " : "")
                    .append(hours)
                    .append(" heure")
                    .append(hours > 1 ? "s" : "");
        }

        if (minutes > 0) {
            sb.append(days > 0 || hours > 0 ? ", " : "")
                    .append(minutes)
                    .append(" minute")
                    .append(minutes > 1 ? "s" : "");
        }

        return sb.toString();
    }

}
