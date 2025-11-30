<?php
/**
 * Database Connection Class
 * Handles all database operations for WorkNPay system
 * 
 * Features:
 * - Connection reuse to avoid multiple connections
 * - Secure error logging without exposing credentials
 * - Query error handling and logging
 */

require_once 'db_cred.php';

if (!class_exists('db_connection')) {
    class db_connection
    {
        public $db = null;
        public $results = null;
        private static $instance = null;

        /**
         * Establish database connection
         * Reuses existing connection if available
         * Logs errors without exposing credentials
         * 
         * @return bool True if connection successful, false otherwise
         */
        function db_connect()
        {
            // Reuse existing connection if available
            if ($this->db !== null && mysqli_ping($this->db)) {
                return true;
            }

            // Establish new connection
            $this->db = @mysqli_connect(SERVER, USERNAME, PASSWD, DATABASE);

            // Test the connection
            if (mysqli_connect_errno()) {
                // Log error without exposing credentials
                error_log("Database connection failed: " . mysqli_connect_error() . " (Error code: " . mysqli_connect_errno() . ")");
                error_log("Database: " . DATABASE . " on server: " . SERVER);
                return false;
            }

            // Set charset to UTF-8
            mysqli_set_charset($this->db, 'utf8mb4');
            
            return true;
        }

        /**
         * Get database connection object
         * Returns the MySQLi connection for direct use
         * 
         * @return mysqli|false Connection object or false on failure
         */
        function db_conn()
        {
            // Reuse existing connection if available
            if ($this->db !== null && mysqli_ping($this->db)) {
                return $this->db;
            }

            // Establish new connection
            $this->db = @mysqli_connect(SERVER, USERNAME, PASSWD, DATABASE);

            // Test the connection
            if (mysqli_connect_errno()) {
                // Log error without exposing credentials
                error_log("Database connection failed: " . mysqli_connect_error() . " (Error code: " . mysqli_connect_errno() . ")");
                error_log("Database: " . DATABASE . " on server: " . SERVER);
                return false;
            }

            // Set charset to UTF-8
            mysqli_set_charset($this->db, 'utf8mb4');
            
            return $this->db;
        }


        /**
         * Execute a database query
         * Logs query errors without exposing SQL to users
         * 
         * @param string $sqlQuery SQL query to execute
         * @return bool True if query successful, false otherwise
         */
        function db_query($sqlQuery)
        {
            if (!$this->db_connect()) {
                error_log("Query failed: Database connection unavailable");
                return false;
            }

            if ($this->db == null) {
                error_log("Query failed: Database connection is null");
                return false;
            }

            try {
                // Run query with error suppression to catch exceptions
                $this->results = @mysqli_query($this->db, $sqlQuery);

                if ($this->results === false) {
                    // Log error without exposing full SQL query to users
                    error_log("Query execution failed: " . mysqli_error($this->db));
                    error_log("Query type: " . strtoupper(substr(trim($sqlQuery), 0, 6)));
                    return false;
                }

                return true;
            } catch (Exception $e) {
                // Catch any exceptions and log them safely
                error_log("Query exception: " . $e->getMessage());
                error_log("Query type: " . strtoupper(substr(trim($sqlQuery), 0, 6)));
                return false;
            }
        }

        /**
         * Execute a write query (INSERT, UPDATE, DELETE)
         * Logs query errors without exposing SQL to users
         * 
         * @param string $sqlQuery SQL query to execute
         * @return bool True if query successful, false otherwise
         */
        function db_write_query($sqlQuery)
        {
            if (!$this->db_connect()) {
                error_log("Write query failed: Database connection unavailable");
                return false;
            }

            if ($this->db == null) {
                error_log("Write query failed: Database connection is null");
                return false;
            }

            try {
                // Run query with error suppression to catch exceptions
                $result = @mysqli_query($this->db, $sqlQuery);

                if ($result === false) {
                    // Log error without exposing full SQL query to users
                    error_log("Write query execution failed: " . mysqli_error($this->db));
                    error_log("Query type: " . strtoupper(substr(trim($sqlQuery), 0, 6)));
                    return false;
                }

                return true;
            } catch (Exception $e) {
                // Catch any exceptions and log them safely
                error_log("Write query exception: " . $e->getMessage());
                error_log("Query type: " . strtoupper(substr(trim($sqlQuery), 0, 6)));
                return false;
            }
        }

        /**
         * Fetch a single row from query results
         * 
         * @param string $sql SQL query to execute
         * @return array|false Associative array of row data or false on failure
         */
        function db_fetch_one($sql)
        {
            if (!$this->db_query($sql)) {
                error_log("Fetch one failed: Query execution error");
                return false;
            }

            $result = mysqli_fetch_assoc($this->results);
            
            if ($result === null) {
                // No rows found - this is not an error, just empty result
                return false;
            }

            return $result;
        }

        /**
         * Fetch all rows from query results
         * 
         * @param string $sql SQL query to execute
         * @return array|false Array of associative arrays or false on failure
         */
        function db_fetch_all($sql)
        {
            // If executing query returns false
            if (!$this->db_query($sql)) {
                error_log("Fetch all failed: Query execution error");
                return false;
            }

            // Return all records
            return mysqli_fetch_all($this->results, MYSQLI_ASSOC);
        }

        /**
         * Get count of rows in last query result
         * 
         * @return int|false Number of rows or false on failure
         */
        function db_count()
        {
            // Check if result was set
            if ($this->results === null || $this->results === false) {
                error_log("Count failed: No query results available");
                return false;
            }

            // Return count
            return mysqli_num_rows($this->results);
        }

        /**
         * Get the ID of the last inserted row
         * 
         * @return int|false Last insert ID or false on failure
         */
        function last_insert_id()
        {
            if ($this->db === null) {
                error_log("Last insert ID failed: No database connection");
                return false;
            }

            return mysqli_insert_id($this->db);
        }

        /**
         * Close database connection
         * Should be called when done with database operations
         */
        function db_close()
        {
            if ($this->db !== null) {
                mysqli_close($this->db);
                $this->db = null;
                $this->results = null;
            }
        }

        /**
         * Escape string for safe SQL usage
         * Prevents SQL injection
         * 
         * @param string $value Value to escape
         * @return string|false Escaped string or false on failure
         */
        function db_escape($value)
        {
            if (!$this->db_connect()) {
                error_log("Escape failed: Database connection unavailable");
                return false;
            }

            return mysqli_real_escape_string($this->db, $value);
        }
    }
}
