-- Add completion_photo field to bookings table
-- This stores the path to the uploaded completion proof photo

ALTER TABLE bookings 
ADD COLUMN completion_photo VARCHAR(255) NULL AFTER customer_notes,
ADD COLUMN completion_notes TEXT NULL AFTER completion_photo;

-- Add index for faster queries
CREATE INDEX idx_completion_photo ON bookings(completion_photo);
