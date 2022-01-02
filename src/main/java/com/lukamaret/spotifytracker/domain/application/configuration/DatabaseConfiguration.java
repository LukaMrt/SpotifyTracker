package com.lukamaret.spotifytracker.domain.application.configuration;

import com.lukamaret.spotifytracker.domain.model.configuration.database.DatabaseCredentials;

public interface DatabaseConfiguration {

    DatabaseCredentials getDatabaseCredentials();

}
