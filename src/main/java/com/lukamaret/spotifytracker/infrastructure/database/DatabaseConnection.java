package com.lukamaret.spotifytracker.infrastructure.database;

import com.zaxxer.hikari.HikariDataSource;

import java.sql.Connection;
import java.sql.SQLException;

public class DatabaseConnection {

    private final HikariDataSource dataSource;

    public DatabaseConnection(DatabaseConnectionBuilder builder) {
        this.dataSource = new HikariDataSource();
        this.dataSource.setJdbcUrl("jdbc:mariadb://" + builder.getHost() + ":" + builder.getPort() + "/" + builder.getDatabase());
        this.dataSource.setUsername(builder.getUser());
        this.dataSource.setPassword(builder.getPassword());
        this.dataSource.addDataSourceProperty("autoReconnect", true);
        this.dataSource.addDataSourceProperty("tcpKeepAlive", true);
        this.dataSource.addDataSourceProperty("serverTimezone", "Europe/Paris");
        this.dataSource.addDataSourceProperty("characterEncoding", "utf8");
        this.dataSource.addDataSourceProperty("useUnicode", "true");
        this.dataSource.setMaximumPoolSize(5);
        this.dataSource.setMinimumIdle(0);
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
