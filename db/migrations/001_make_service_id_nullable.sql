-- Migration: Make service_id nullable in bookings table
-- Date: 2025-11-24
-- Purpose: Allow bookings to be created without a specific service reference
-- Requirements: 5.4, 5.5

USE `worknpay`;

-- Step 1: Drop the existing foreign key constraint
ALTER TABLE `bookings` 
DROP FOREIGN KEY `fk_booking_service`;

-- Step 2: Modify the service_id column to allow NULL
ALTER TABLE `bookings` 
MODIFY COLUMN `service_id` int NULL;

-- Step 3: Re-add the foreign key constraint with ON DELETE SET NULL
ALTER TABLE `bookings` 
ADD CONSTRAINT `fk_booking_service` 
  FOREIGN KEY (`service_id`) 
  REFERENCES `services` (`service_id`) 
  ON DELETE SET NULL;

-- Verification query (run this to confirm the change)
-- SELECT 
--   COLUMN_NAME, 
--   IS_NULLABLE, 
--   COLUMN_TYPE 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = 'worknpay' 
--   AND TABLE_NAME = 'bookings' 
--   AND COLUMN_NAME = 'service_id';

-- Expected result: IS_NULLABLE should be 'YES'
