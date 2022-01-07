package com.lukamaret.spotifytracker.domain.application.spotify;

import com.lukamaret.spotifytracker.domain.application.configuration.DiscordConfiguration;
import com.lukamaret.spotifytracker.domain.application.message.MessageSender;
import com.lukamaret.spotifytracker.domain.model.spotify.*;

import javax.inject.Inject;
import java.time.DayOfWeek;
import java.time.LocalDate;
import java.time.LocalDateTime;
import java.time.temporal.TemporalAdjusters;
import java.util.List;

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

        LocalDate today = LocalDate.now();
        LocalDate yesterday = today.minusDays(1);
        SpotifyReport report = buildReport(yesterday, today);

        String dailyChannel = discordConfiguration.getDailyChannel();
        messageSender.sendReport(dailyChannel, report);
    }

    private void sendWeeklyReport() {

        LocalDate currentWeek = LocalDate.now().with(TemporalAdjusters.previous(DayOfWeek.MONDAY));
        LocalDate nextWeek = LocalDate.now().with(TemporalAdjusters.next(DayOfWeek.MONDAY));
        SpotifyReport report = buildReport(currentWeek, nextWeek);

        String dailyChannel = discordConfiguration.getWeeklyChannel();
        messageSender.sendReport(dailyChannel, report);
    }

    private void sendMonthlyReport() {

        LocalDate currentMonth = LocalDate.now().withDayOfMonth(1);
        LocalDate nextMonth = LocalDate.now().plusMonths(1).withDayOfMonth(1);
        SpotifyReport report = buildReport(currentMonth, nextMonth);

        String dailyChannel = discordConfiguration.getMonthlyChannel();
        messageSender.sendReport(dailyChannel, report);
    }

    private void sendYearlyReport() {

        LocalDate currentYear = LocalDate.now().withDayOfYear(1);
        LocalDate nextYear = LocalDate.now().plusYears(1).withDayOfYear(1);
        SpotifyReport report = buildReport(currentYear, nextYear);

        String dailyChannel = discordConfiguration.getYearlyChannel();
        messageSender.sendReport(dailyChannel, report);
    }

    private SpotifyReport buildReport(LocalDate start, LocalDate end) {

        int listeningMinutes = listeningRepository.getListeningMinutes(start, end);
        int trackCount = listeningRepository.getTracksCount(start, end);
        int artistsCount = listeningRepository.getArtistsCount(start, end);
        int playlistsCount = listeningRepository.getPlaylistsCount(start, end);

        List<Track> mostPlayedTracks = listeningRepository.getMostPlayedTracks(start, end);
        List<Artist> mostPlayedArtists = listeningRepository.getMostPlayedArtists(start, end);
        List<Playlist> mostPlayedPlaylists = listeningRepository.getMostPlayedPlaylists(start, end);

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
