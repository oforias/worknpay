-- Migration: Create worker_payout_accounts table
-- Purpose: Store worker payout account details for withdrawals
-- Date: 2024-01-28

-- Create worker_payout_accounts table
CREATE TABLE IF NOT EXISTS worker_payout_accounts (
    account_id INT PRIMARY KEY AUTO_INCREMENT,
    worker_id INT NOT NULL,
    account_type ENUM('mobile_money', 'bank_transfer') NOT NULL,
    
    -- Mobile Money fields
    mobile_number VARCHAR(15) DEFAULT NULL,
    mobile_network ENUM('MTN', 'Vodafone', 'Telecel') DEFAULT NULL,
    
    -- Bank Transfer fields
    bank_name VARCHAR(100) DEFAULT NULL,
    account_number VARCHAR(50) DEFAULT NULL,
    account_holder_name VARCHAR(100) DEFAULT NULL,
    
    -- Account settings
    is_default TINYINT(1) DEFAULT 0,
    is_verified TINYINT(1) DEFAULT 0,
    
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Foreign key constraint
    FOREIGN KEY (worker_id) REFERENCES users(user_id) ON DELETE CASCADE,
    
    -- Indexes
    INDEX idx_worker_id (worker_id),
    INDEX idx_is_default (is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add worker_response fields to disputes table for worker counter-evidence
ALTER TABLE disputes 
ADD COLUMN IF NOT EXISTS worker_response TEXT DEFAULT NULL AFTER dispute_description,
ADD COLUMN IF NOT EXISTS worker_evidence_photos TEXT DEFAULT NULL AFTER worker_response,
ADD COLUMN IF NOT EXISTS worker_response_date TIMESTAMP NULL DEFAULT NULL AFTER worker_evidence_photos;

-- Verify tables exist
SELECT 'Migration 003 completed successfully' as status;
SELECT COUNT(*) as payout_accounts_count FROM worker_payout_accounts;
