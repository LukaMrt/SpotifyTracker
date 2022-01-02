package com.lukamaret.spotifytracker.domain.model.configuration.discord;

public class DiscordConfiguration {

    private String token = "token";
    private String logs = "log_channel_id";
    private String guard = "guard_bot_id";

    public String getToken() {
        return token;
    }

    public String getLogs() {
        return logs;
    }

    public String getGuard() {
        return guard;
    }

}
