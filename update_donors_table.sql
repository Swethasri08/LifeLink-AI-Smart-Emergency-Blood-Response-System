-- Add missing columns to donors table
ALTER TABLE donors
ADD COLUMN last_donation_date DATE NULL,
ADD COLUMN age INT NULL,
ADD COLUMN weight DECIMAL(5,2) NULL,
ADD COLUMN has_health_condition BOOLEAN DEFAULT FALSE,
ADD COLUMN health_condition_details TEXT NULL,
ADD COLUMN is_eligible BOOLEAN DEFAULT FALSE,
ADD COLUMN eligibility_checked_at TIMESTAMP NULL; 