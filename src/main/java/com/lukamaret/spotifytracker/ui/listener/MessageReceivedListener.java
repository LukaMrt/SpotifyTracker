package com.lukamaret.spotifytracker.ui.listener;

import com.google.inject.Injector;
import com.lukamaret.spotifytracker.domain.model.commands.Command;
import com.lukamaret.spotifytracker.ui.command.PingCommand;
import com.lukamaret.spotifytracker.ui.command.StopCommand;
import org.javacord.api.event.message.MessageCreateEvent;
import org.javacord.api.listener.message.MessageCreateListener;

import java.util.Arrays;
import java.util.List;

public class MessageReceivedListener implements MessageCreateListener {

    private final List<Command> commands;

    public MessageReceivedListener(Injector injector) {
        this.commands = List.of(
                injector.getInstance(StopCommand.class),
                injector.getInstance(PingCommand.class)
        );
    }

    @Override
    public void onMessageCreate(MessageCreateEvent event) {

        if (event.getMessageAuthor().getId() == event.getApi().getYourself().getId()) {
            return;
        }

        if (!event.getMessageContent().startsWith("!")) {
            return;
        }

        String[] content = event.getMessageContent().split(" ");
        String commandName = content[0].substring(1);
        long authorId = event.getMessageAuthor().getId();
        long channelId = event.getChannel().getId();
        String[] args = Arrays.copyOfRange(content, 1, content.length);

        this.commands.stream()
                .filter(command -> command.getName().equals(commandName))
                .forEach(command -> command.execute(authorId, channelId, args));
    }

}
