package com.lukamaret.spotifytracker.infrastructure.tasks;

import com.google.inject.Injector;
import com.lukamaret.spotifytracker.domain.application.tasks.TasksRepository;
import com.lukamaret.spotifytracker.ui.task.PeriodsReportTask;
import com.lukamaret.spotifytracker.ui.task.SpotifyTrackerTask;

import java.time.Duration;
import java.time.LocalDateTime;
import java.util.Timer;

public class DefaultTasksRepository implements TasksRepository {

    public static final int THIRTY_SECONDS = 30 * 1_000;
    public static final int ONE_DAY = 24 * 60 * 60 * 1_000;
    public static final int REPORT_HOUR = 8;


    private final Injector injector;

    public DefaultTasksRepository(Injector injector) {
        this.injector = injector;
    }

    @Override
    public void registerTasks() {

        LocalDateTime now = LocalDateTime.now();

        LocalDateTime next8Hours = now.withHour(REPORT_HOUR).withMinute(0).withSecond(0);

        if (now.getHour() >= REPORT_HOUR) {
            next8Hours = next8Hours.plusDays(1);
        }

        long delay = Duration.between(now, next8Hours).toMillis();

        new Timer().schedule(injector.getInstance(SpotifyTrackerTask.class), 0, THIRTY_SECONDS);
        new Timer().schedule(injector.getInstance(PeriodsReportTask.class), delay, ONE_DAY);
    }

}
