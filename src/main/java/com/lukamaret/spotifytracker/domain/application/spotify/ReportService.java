package com.lukamaret.spotifytracker.domain.application.spotify;

import com.lukamaret.spotifytracker.domain.application.configuration.DiscordConfiguration;
import com.lukamaret.spotifytracker.domain.application.message.MessageSender;
import com.lukamaret.spotifytracker.domain.model.spotify.report.*;

import javax.inject.Inject;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.format.TextStyle;
import java.time.temporal.TemporalAdjusters;
import java.util.List;
import java.util.Locale;

public class ReportService {

    @Inject
    private DiscordConfiguration discordConfiguration;

    @Inject
    private ListeningRepository listeningRepository;

    @Inject
    private MessageSender messageSender;

    public void sendDailyReports() {

        LocalDateTime now = LocalDateTime.now();

        sendDailyReport();

        if (now.getDayOfWeek() == DayOfWeek.MONDAY) {
            sendWeeklyReport();
        }

        if (now.getDayOfMonth() == 1) {
            sendMonthlyReport();
        }

        if (now.getDayOfYear() == 1) {
            sendYearlyReport();
        }

    }

    public void sendAllDailyReports() {

        sendDailyReports();
        sendWeeklyReport();
        sendMonthlyReport();
        sendYearlyReport();

    }

    private void sendDailyReport() {

        LocalDate yesterday = LocalDate.now().minusDays(1);
        LocalDate today = LocalDate.now();
        SpotifyReport report = buildReport(yesterday, today);

        String dailyChannel = discordConfiguration.getDailyChannel();
        String title = "Spotify report : "
                + yesterday.getDayOfWeek().getDisplayName(TextStyle.FULL, Locale.FRANCE)
                + " "
                + String.format("%02d", yesterday.getDayOfMonth())
                + "/"
                + String.format("%02d", yesterday.getMonthValue())
                + "/"
                + today.getYear();
        messageSender.sendReport(dailyChannel, report, title);
    }

    private void sendWeeklyReport() {

        LocalDate lastWeek = LocalDate.now().minusWeeks(1).plusDays(1).with(TemporalAdjusters.previous(DayOfWeek.MONDAY));
        LocalDate today = LocalDate.now().plusDays(1).with(TemporalAdjusters.previous(DayOfWeek.MONDAY));
        SpotifyReport report = buildReport(lastWeek, today);

        String dailyChannel = discordConfiguration.getWeeklyChannel();
        String title = "Spotify report : "
                + "semaine du lundi "
                + String.format("%02d", lastWeek.getDayOfMonth())
                + "/"
                + String.format("%02d", lastWeek.getMonthValue())
                + "/"
                + lastWeek.getYear()
                + " au dimanche "
                + String.format("%02d", today.getDayOfMonth())
                + "/"
                + String.format("%02d", today.getMonthValue())
                + "/"
                + today.getYear();

        messageSender.sendReport(dailyChannel, report, title);
    }

    private void sendMonthlyReport() {

        LocalDate lastMonth = LocalDate.now().minusMonths(1).withDayOfMonth(1);
        LocalDate today = LocalDate.now().withDayOfMonth(1);
        SpotifyReport report = buildReport(lastMonth, today);

        String dailyChannel = discordConfiguration.getMonthlyChannel();
        String title = "Spotify report : mois de "
                + lastMonth.getMonth().getDisplayName(TextStyle.FULL, Locale.FRANCE)
                + " "
                + lastMonth.getYear();

        messageSender.sendReport(dailyChannel, report, title);
    }

    private void sendYearlyReport() {

        LocalDate lastYear = LocalDate.now().minusYears(1).withDayOfYear(1);
        LocalDate today = LocalDate.now().withDayOfYear(1);
        SpotifyReport report = buildReport(lastYear, today);

        String dailyChannel = discordConfiguration.getYearlyChannel();
        String title = "Spotify report : année " + lastYear.getYear();

        messageSender.sendReport(dailyChannel, report, title);
    }

    private SpotifyReport buildReport(LocalDate start, LocalDate end) {

        int listeningMinutes = listeningRepository.getListeningMinutes(start, end);
        int trackCount = listeningRepository.getTracksCount(start, end);
        int artistsCount = listeningRepository.getArtistsCount(start, end);
        int playlistsCount = listeningRepository.getPlaylistsCount(start, end);

        List<ReportTrack> mostPlayedTracks = listeningRepository.getMostPlayedTracks(start, end);
        List<ReportArtist> mostPlayedArtists = listeningRepository.getMostPlayedArtists(start, end);
        List<ReportPlaylist> mostPlayedPlaylists = listeningRepository.getMostPlayedPlaylists(start, end);

        return SpotifyReportBuilder.aSpotifyReport()
                .withListeningMinutes(listeningMinutes)
                .withTracksCount(trackCount)
                .withArtistsCount(artistsCount)
                .withPlaylistsCount(playlistsCount)
                .withMostPlayedTracks(mostPlayedTracks)
                .withMostPlayedArtists(mostPlayedArtists)
                .withMostPlayedPlaylists(mostPlayedPlaylists)
                .build();
    }

}
