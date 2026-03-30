-- Update database schema to match application requirements
USE bdms;

-- Add missing columns to donors table (skip if already exist)
ALTER TABLE donors 
ADD COLUMN age INT AFTER blood_type,
ADD COLUMN weight DECIMAL(5,2) AFTER age,
ADD COLUMN last_donation_date DATE NULL AFTER weight,
ADD COLUMN has_health_condition TINYINT(1) DEFAULT 0 AFTER last_donation_date,
ADD COLUMN health_condition_details TEXT NULL AFTER has_health_condition,
ADD COLUMN is_eligible TINYINT(1) DEFAULT 1 AFTER health_condition_details,
ADD COLUMN eligibility_checked_at TIMESTAMP NULL AFTER is_eligible;
