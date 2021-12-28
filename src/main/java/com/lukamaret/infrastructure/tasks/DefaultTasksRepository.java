package com.lukamaret.infrastructure.tasks;

import com.google.inject.Injector;
import com.lukamaret.ui.task.SpotifyTrackerTask;

import java.util.List;
import java.util.Timer;
import java.util.TimerTask;

public class DefaultTasksRepository {

    private final List<TimerTask> tasks;

    public DefaultTasksRepository(Injector injector) {
        this.tasks = List.of(
                injector.getInstance(SpotifyTrackerTask.class)
        );
    }

    public void registerTasks() {

        tasks.forEach(task -> {
            Timer timer = new Timer();
            timer.schedule(task, 0, 60 * 1000);
        });

    }

}
