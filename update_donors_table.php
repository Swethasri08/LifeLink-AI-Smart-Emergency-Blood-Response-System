<?php
require_once 'config/database.php';

// SQL to add missing columns
$sql = "ALTER TABLE donors
        ADD COLUMN IF NOT EXISTS last_donation_date DATE NULL,
        ADD COLUMN IF NOT EXISTS age INT NULL,
        ADD COLUMN IF NOT EXISTS weight DECIMAL(5,2) NULL,
        ADD COLUMN IF NOT EXISTS has_health_condition BOOLEAN DEFAULT FALSE,
        ADD COLUMN IF NOT EXISTS health_condition_details TEXT NULL,
        ADD COLUMN IF NOT EXISTS is_eligible BOOLEAN DEFAULT FALSE,
        ADD COLUMN IF NOT EXISTS eligibility_checked_at TIMESTAMP NULL";

if ($conn->query($sql)) {
    echo "Donors table updated successfully";
} else {
    echo "Error updating donors table: " . $conn->error;
}
?> 