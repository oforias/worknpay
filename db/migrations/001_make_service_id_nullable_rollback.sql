-- Rollback Migration: Revert service_id to NOT NULL in bookings table
-- Date: 2025-11-24
-- Purpose: Rollback the nullable service_id change if needed
-- WARNING: This will fail if any bookings have NULL service_id

USE `worknpay`;

-- Step 1: Check if any bookings have NULL service_id
-- SELECT COUNT(*) FROM bookings WHERE service_id IS NULL;
-- If count > 0, you must either:
--   a) Delete those bookings, OR
--   b) Update them with a valid service_id before rolling back

-- Step 2: Drop the existing foreign key constraint
ALTER TABLE `bookings` 
DROP FOREIGN KEY `fk_booking_service`;

-- Step 3: Modify the service_id column back to NOT NULL
-- WARNING: This will fail if any NULL values exist
ALTER TABLE `bookings` 
MODIFY COLUMN `service_id` int NOT NULL;

-- Step 4: Re-add the original foreign key constraint
ALTER TABLE `bookings` 
ADD CONSTRAINT `fk_booking_service` 
  FOREIGN KEY (`service_id`) 
  REFERENCES `services` (`service_id`);

-- Verification query
-- SELECT 
--   COLUMN_NAME, 
--   IS_NULLABLE, 
--   COLUMN_TYPE 
-- FROM INFORMATION_SCHEMA.COLUMNS 
-- WHERE TABLE_SCHEMA = 'worknpay' 
--   AND TABLE_NAME = 'bookings' 
--   AND COLUMN_NAME = 'service_id';

-- Expected result: IS_NULLABLE should be 'NO'
