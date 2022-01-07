package com.lukamaret.spotifytracker.infrastructure.tasks;

import com.google.inject.Injector;
import com.lukamaret.spotifytracker.domain.application.tasks.TasksRepository;
import com.lukamaret.spotifytracker.ui.task.DailyCleanerTask;
import com.lukamaret.spotifytracker.ui.task.PeriodsReportTask;
import com.lukamaret.spotifytracker.ui.task.SpotifyTrackerTask;

import java.time.Duration;
import java.time.LocalDateTime;
import java.util.Timer;

public class DefaultTasksRepository implements TasksRepository {

    private final Injector injector;

    public DefaultTasksRepository(Injector injector) {
        this.injector = injector;
    }

    @Override
    public void registerTasks() {

        LocalDateTime now = LocalDateTime.now();

        LocalDateTime next8Hours = now.withHour(8);

        if (now.getHour() >= 8) {
            next8Hours = next8Hours.plusDays(1);
        }

        long delay = Duration.between(now, next8Hours).toMillis();

        new Timer().schedule(injector.getInstance(SpotifyTrackerTask.class), 0, 30 * 1000);
        new Timer().schedule(injector.getInstance(PeriodsReportTask.class), delay, 24 * 60 * 60 * 1000);
        new Timer().schedule(injector.getInstance(DailyCleanerTask.class), delay, 24 * 60 * 60 * 1000);
    }

}
