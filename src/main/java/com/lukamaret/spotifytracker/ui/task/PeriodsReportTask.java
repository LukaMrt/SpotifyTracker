package com.lukamaret.spotifytracker.ui.task;

import com.lukamaret.spotifytracker.domain.application.spotify.ReportService;

import javax.inject.Inject;
import java.util.TimerTask;

public class PeriodsReportTask extends TimerTask {

    @Inject
    private ReportService reportService;

    @Override
    public void run() {
        reportService.sendDailyReports();
    }

}
