package com.lukamaret.spotifytracker.domain.application.configuration;

public interface DiscordConfiguration {

    String getToken();

    String getTrackingChannel();

    String getDailyChannel();

    String getWeeklyChannel();

    String getMonthlyChannel();

    String getYearlyChannel();

}
