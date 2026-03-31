<?php
// Demo Database Configuration for Blood Donation Management System
// This version works without a database for demo purposes

// Create a mock database connection for demo
class DemoDatabase {
    public $connected = true;
    
    public function query($sql) {
        // Return demo data for common queries
        if (strpos($sql, 'SELECT') !== false) {
            if (strpos($sql, 'donors') !== false) {
                return [
                    (object)['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com', 'blood_type' => 'O+'],
                    (object)['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com', 'blood_type' => 'A+']
                ];
            }
        }
        return true;
    }
    
    public function fetch_assoc($result) {
        if (is_array($result)) {
            return array_shift($result);
        }
        return null;
    }
    
    public function num_rows($result) {
        if (is_array($result)) {
            return count($result);
        }
        return 0;
    }
}

// Create demo connection
$conn = new DemoDatabase();

// For demo purposes, we'll simulate successful database connection
// In production, replace this with actual database connection

?>
