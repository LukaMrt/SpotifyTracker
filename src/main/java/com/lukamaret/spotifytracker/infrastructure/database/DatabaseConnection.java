package com.lukamaret.spotifytracker.infrastructure.database;

import com.zaxxer.hikari.HikariDataSource;

import java.sql.Connection;
import java.sql.SQLException;

public class DatabaseConnection {

    private final HikariDataSource dataSource;

    public DatabaseConnection(DatabaseConnectionBuilder builder) {
        this.dataSource = new HikariDataSource();
        setUpDataSource(builder);
    }

    private void setUpDataSource(DatabaseConnectionBuilder builder) {
        dataSource.setJdbcUrl("jdbc:postgresql://" + builder.getHost() + ":" + builder.getPort() + "/" + builder.getDatabase());
        dataSource.setUsername(builder.getUser());
        dataSource.setPassword(builder.getPassword());
        dataSource.addDataSourceProperty("autoReconnect", true);
        dataSource.addDataSourceProperty("tcpKeepAlive", true);
        dataSource.addDataSourceProperty("serverTimezone", "Europe/Paris");
        dataSource.addDataSourceProperty("characterEncoding", "utf8");
        dataSource.addDataSourceProperty("useUnicode", "true");
        dataSource.setMaximumPoolSize(15);
        dataSource.setMinimumIdle(0);
    }

    public Connection getConnection() {
        try {
            return dataSource.getConnection();
        } catch (SQLException exception) {
            exception.printStackTrace();
            return null;
        }
    }

}
