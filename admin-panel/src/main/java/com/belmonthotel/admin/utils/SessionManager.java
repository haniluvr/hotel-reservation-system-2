package com.belmonthotel.admin.utils;

import java.time.LocalDateTime;

/**
 * Singleton class for managing admin session information.
 */
public class SessionManager {
    private static SessionManager instance;
    
    private Integer userId;
    private String userName;
    private String userEmail;
    private LocalDateTime loginTime;
    private boolean isAdmin;

    private SessionManager() {
        // Private constructor for singleton
    }

    /**
     * Get the singleton instance of SessionManager.
     * @return SessionManager instance
     */
    public static synchronized SessionManager getInstance() {
        if (instance == null) {
            instance = new SessionManager();
        }
        return instance;
    }

    /**
     * Set the current session user information.
     * @param userId User ID
     * @param userName User name
     * @param userEmail User email
     * @param isAdmin Whether user is admin
     */
    public void setSession(Integer userId, String userName, String userEmail, boolean isAdmin) {
        this.userId = userId;
        this.userName = userName;
        this.userEmail = userEmail;
        this.isAdmin = isAdmin;
        this.loginTime = LocalDateTime.now();
    }

    /**
     * Clear the current session.
     */
    public void clearSession() {
        this.userId = null;
        this.userName = null;
        this.userEmail = null;
        this.isAdmin = false;
        this.loginTime = null;
    }

    /**
     * Check if a user is currently logged in.
     * @return true if user is logged in
     */
    public boolean isLoggedIn() {
        return userId != null;
    }

    // Getters
    public Integer getUserId() {
        return userId;
    }

    public String getUserName() {
        return userName;
    }

    public String getUserEmail() {
        return userEmail;
    }

    public LocalDateTime getLoginTime() {
        return loginTime;
    }

    public boolean isAdmin() {
        return isAdmin;
    }
}

