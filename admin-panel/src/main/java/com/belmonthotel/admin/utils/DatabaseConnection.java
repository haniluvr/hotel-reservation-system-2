package com.belmonthotel.admin.utils;

import com.zaxxer.hikari.HikariConfig;
import com.zaxxer.hikari.HikariDataSource;

import java.io.InputStream;
import java.sql.Connection;
import java.sql.SQLException;
import java.util.Properties;

/**
 * Singleton class for managing database connections using HikariCP connection pooling.
 */
public class DatabaseConnection {
    private static DatabaseConnection instance;
    private static HikariDataSource dataSource;

    private DatabaseConnection() {
        try {
            // Load configuration from properties file
            Properties props = new Properties();
            InputStream inputStream = getClass().getClassLoader().getResourceAsStream("config.properties");
            
            if (inputStream == null) {
                throw new RuntimeException("config.properties file not found in resources");
            }
            
            props.load(inputStream);

            // Configure HikariCP
            HikariConfig config = new HikariConfig();
            config.setJdbcUrl("jdbc:mysql://" + 
                props.getProperty("db.host", "127.0.0.1") + ":" + 
                props.getProperty("db.port", "3306") + "/" + 
                props.getProperty("db.database", "hotel_db"));
            config.setUsername(props.getProperty("db.username", "root"));
            config.setPassword(props.getProperty("db.password", ""));
            config.setDriverClassName("com.mysql.cj.jdbc.Driver");
            
            // Connection pool settings
            config.setMaximumPoolSize(10);
            config.setMinimumIdle(2);
            config.setConnectionTimeout(30000);
            config.setIdleTimeout(600000);
            config.setMaxLifetime(1800000);
            config.setLeakDetectionThreshold(60000);
            
            // MySQL-specific settings
            config.addDataSourceProperty("cachePrepStmts", "true");
            config.addDataSourceProperty("prepStmtCacheSize", "250");
            config.addDataSourceProperty("prepStmtCacheSqlLimit", "2048");
            config.addDataSourceProperty("useServerPrepStmts", "true");
            config.addDataSourceProperty("useLocalSessionState", "true");
            config.addDataSourceProperty("rewriteBatchedStatements", "true");
            config.addDataSourceProperty("cacheResultSetMetadata", "true");
            config.addDataSourceProperty("cacheServerConfiguration", "true");
            config.addDataSourceProperty("elideSetAutoCommits", "true");
            config.addDataSourceProperty("maintainTimeStats", "false");

            dataSource = new HikariDataSource(config);
            
        } catch (Exception e) {
            throw new RuntimeException("Failed to initialize database connection pool", e);
        }
    }

    /**
     * Get the singleton instance of DatabaseConnection.
     * @return DatabaseConnection instance
     */
    public static synchronized DatabaseConnection getInstance() {
        if (instance == null) {
            instance = new DatabaseConnection();
        }
        return instance;
    }

    /**
     * Get a connection from the connection pool.
     * @return Connection object
     * @throws SQLException if connection fails
     */
    public static Connection getConnection() throws SQLException {
        if (dataSource == null) {
            getInstance();
        }
        return dataSource.getConnection();
    }

    /**
     * Close the connection pool and all connections.
     */
    public static void closeConnection() {
        if (dataSource != null && !dataSource.isClosed()) {
            dataSource.close();
        }
    }

    /**
     * Check if the connection pool is healthy.
     * @return true if connection pool is active
     */
    public static boolean isHealthy() {
        return dataSource != null && !dataSource.isClosed();
    }
}

