package com.lukamaret.spotifytracker.view.message;

import com.lukamaret.spotifytracker.domain.application.message.MessageSender;
import com.lukamaret.spotifytracker.domain.model.spotify.Playlist;
import com.lukamaret.spotifytracker.domain.model.spotify.Track;
import org.javacord.api.DiscordApi;
import org.javacord.api.entity.message.embed.EmbedBuilder;

import javax.inject.Inject;
import java.awt.*;

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

}
