package com.lukamaret.domain.application.configuration;

import com.lukamaret.domain.model.configuration.database.DatabaseCredentials;

public interface DatabaseConfiguration {

    DatabaseCredentials getDatabaseCredentials();

}
