package com.lukamaret.spotifytracker.infrastructure.tasks;

import com.google.inject.Injector;
import com.lukamaret.spotifytracker.ui.task.SpotifyTrackerTask;

import java.util.List;
import java.util.Timer;
import java.util.TimerTask;

public class DefaultTasksRepository {

    public static final int PERIOD = 30 * 1000;

    private final List<TimerTask> tasks;

    public DefaultTasksRepository(Injector injector) {
        this.tasks = List.of(
                injector.getInstance(SpotifyTrackerTask.class)
        );
    }

    public void registerTasks() {
        tasks.forEach(task -> new Timer().schedule(task, 0, PERIOD));
    }

}
