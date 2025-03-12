<?php

class SessionAuth {
    private $username;
    private $password;

    // Start the session and initialize the class
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Load credentials from settings.json
        $this->loadCredentials();
    }

    // Load credentials from settings.json
    private function loadCredentials() {
        $settingsFilePath = 'settings.json';

        if (!file_exists($settingsFilePath)) {
            throw new Exception('Settings file not found.');
        }

        if (!is_readable($settingsFilePath)) {
            throw new Exception('Unable to read settings file. Check permissions.');
        }

        $settings = json_decode(file_get_contents($settingsFilePath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Invalid JSON in settings file.');
        }

        if (!isset($settings['username']) || !isset($settings['password'])) {
            throw new Exception('Invalid settings file. Missing username or password.');
        }

        $this->username = $settings['username'];
        $this->password = $settings['password'];
    }

    // Log in a user
    public function login($username, $password) {
        if ($username === $this->username && $password === $this->password) {
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $username;
            return true;
        }

        return false;
    }

    // Log out the current user
    public function logout() {
        session_unset();
        session_destroy();
    }

    // Check if the user is authenticated
    public function isAuthenticated() {
        return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
    }

    // Get the current username
    public function getUsername() {
        return $_SESSION['username'] ?? null;
    }
}