package com.lukamaret.spotifytracker.ui.command;

import com.lukamaret.spotifytracker.domain.application.spotify.ReportService;
import com.lukamaret.spotifytracker.domain.model.commands.Command;

import javax.inject.Inject;

public class ReportCommand implements Command {

    @Inject
    private ReportService reportService;

    @Override
    public String getName() {
        return "report";
    }

    @Override
    public void execute(long authorId, long channelId, String[] args) {
        reportService.sendAllDailyReports();
    }

}
