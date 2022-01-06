package com.lukamaret.spotifytracker.domain.model.configuration.discord;

public class DiscordConfiguration {

    private String token = "token";
    private String tracking = "channel_id";
    private String daily = "channel_id";
    private String weekly = "channel_id";
    private String monthly = "channel_id";
    private String yearly = "channel_id";

    public String getToken() {
        return token;
    }

    public String getTracking() {
        return tracking;
    }

    public String getDaily() {
        return daily;
    }

    public String getWeekly() {
        return weekly;
    }

    public String getMonthly() {
        return monthly;
    }

    public String getYearly() {
        return yearly;
    }

}
