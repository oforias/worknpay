<?php
/**
 * Environment Detection and Configuration
 * Automatically detects the current environment and sets appropriate configurations
 */

/**
 * Detect if we're on a live server or localhost
 * 
 * @return string 'production', 'staging', or 'development'
 */
function detect_environment() {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // Check for localhost
    if (strpos($host, 'localhost') !== false || 
        strpos($host, '127.0.0.1') !== false ||
        strpos($host, '::1') !== false) {
        return 'development';
    }
    
    // Check for staging/test domains
    if (strpos($host, 'staging.') !== false || 
        strpos($host, 'test.') !== false ||
        strpos($host, 'dev.') !== false) {
        return 'staging';
    }
    
    // Everything else is production
    return 'production';
}

/**
 * Get the full base URL of the application
 * 
 * @return string Full base URL (e.g., https://example.com/app)
 */
function get_base_url() {
    // Detect protocol
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                 (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                 ? 'https://' : 'http://';
    
    // Get host
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    
    // For InfinityFree and most hosting, files are in root (htdocs)
    // So base URL is just protocol + host
    if (strpos($host, 'infinityfreeapp.com') !== false || 
        strpos($host, 'epizy.com') !== false ||
        strpos($host, '000webhostapp.com') !== false) {
        // Free hosting - files are in root
        return $protocol . $host;
    }
    
    // For localhost, detect the folder
    if (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) {
        $script_name = $_SERVER['SCRIPT_NAME'] ?? '';
        $base_path = '';
        
        // Remove common subdirectories to get the base path
        $subdirs = ['/actions/', '/view/', '/settings/', '/classes/', '/controllers/', '/db/', '/test_'];
        
        foreach ($subdirs as $subdir) {
            if (strpos($script_name, $subdir) !== false) {
                $base_path = substr($script_name, 0, strpos($script_name, $subdir));
                break;
            }
        }
        
        // If no subdirectory found, use the directory of the script
        if (empty($base_path)) {
            $base_path = dirname($script_name);
            if ($base_path === '/' || $base_path === '\\') {
                $base_path = '';
            }
        }
        
        return $protocol . $host . $base_path;
    }
    
    // For other hosting, assume root
    return $protocol . $host;
}

/**
 * Get the current page URL
 * 
 * @return string Full current URL
 */
function get_current_url() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
                 (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
                 (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                 ? 'https://' : 'http://';
    
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    
    return $protocol . $host . $uri;
}

/**
 * Check if we're on HTTPS
 * 
 * @return bool True if HTTPS, false otherwise
 */
function is_https() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || 
           (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) ||
           (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

/**
 * Check if we're on localhost
 * 
 * @return bool True if localhost, false otherwise
 */
function is_localhost() {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    return strpos($host, 'localhost') !== false || 
           strpos($host, '127.0.0.1') !== false ||
           strpos($host, '::1') !== false;
}

/**
 * Get environment-specific configuration
 * 
 * @param string $key Configuration key
 * @param mixed $default Default value if not found
 * @return mixed Configuration value
 */
function get_env_config($key, $default = null) {
    $env = detect_environment();
    
    $config = [
        'development' => [
            'debug' => true,
            'error_reporting' => E_ALL,
            'display_errors' => true,
            'log_errors' => true,
            'paystack_mode' => 'test',
        ],
        'staging' => [
            'debug' => true,
            'error_reporting' => E_ALL,
            'display_errors' => false,
            'log_errors' => true,
            'paystack_mode' => 'test',
        ],
        'production' => [
            'debug' => false,
            'error_reporting' => E_ERROR | E_WARNING,
            'display_errors' => false,
            'log_errors' => true,
            'paystack_mode' => 'live',
        ]
    ];
    
    return $config[$env][$key] ?? $default;
}

/**
 * Apply environment-specific PHP settings
 */
function apply_environment_settings() {
    $env = detect_environment();
    
    // Set error reporting based on environment
    error_reporting(get_env_config('error_reporting', E_ALL));
    ini_set('display_errors', get_env_config('display_errors', false) ? '1' : '0');
    ini_set('log_errors', get_env_config('log_errors', true) ? '1' : '0');
    
    // Set timezone
    date_default_timezone_set('Africa/Accra');
    
    // Log environment detection
    error_log("Environment detected: $env");
    error_log("Base URL: " . get_base_url());
    error_log("HTTPS: " . (is_https() ? 'Yes' : 'No'));
}

// Auto-apply settings when this file is included
apply_environment_settings();
?>
