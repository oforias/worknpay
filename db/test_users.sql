-- Test Users for WorkNPay Platform
-- Password for all test users: password123
-- Hashed using PHP password_hash() with PASSWORD_DEFAULT

-- Clear existing test data (optional - uncomment if needed)
-- DELETE FROM bookings WHERE customer_id IN (SELECT customer_id FROM customer WHERE user_id IN (1,2,3,4,5));
-- DELETE FROM worker_profiles WHERE user_id IN (1,2,3,4,5);
-- DELETE FROM customer WHERE user_id IN (1,2,3,4,5);
-- DELETE FROM user WHERE user_id IN (1,2,3,4,5);

-- Insert Test Users
-- Note: Password hash for 'password123' - you may need to regenerate this using PHP password_hash()
INSERT INTO user (user_id, customer_name, customer_email, customer_pass, customer_contact, customer_country, user_role) VALUES
(1, 'John Customer', 'customer@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0244123456', 'Ghana', 1),
(2, 'Sarah Buyer', 'sarah@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0244234567', 'Ghana', 1),
(3, 'Mike Worker', 'worker@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0244345678', 'Ghana', 2),
(4, 'Grace Electrician', 'grace@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0244456789', 'Ghana', 2),
(5, 'Admin User', 'admin@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0244567890', 'Ghana', 3);

-- Insert Customer Records
INSERT INTO customer (customer_id, user_id) VALUES
(1, 1),
(2, 2);

-- Insert Worker Profiles
INSERT INTO worker_profiles (user_id, skills, hourly_rate, bio, years_experience, total_jobs_completed, average_rating, available_balance, verification_status, created_at) VALUES
(3, 'Phone Repair, Laptop Repair, Tablet Repair', 50.00, 'Experienced gadget repair specialist with 5+ years in the field. I fix phones, laptops, and tablets quickly and professionally.', 5, 12, 4.8, 450.00, 'verified', NOW()),
(4, 'Electrical Wiring, Appliance Repair, Installations', 75.00, 'Licensed electrician specializing in home and office electrical work. Safety and quality are my priorities.', 8, 28, 4.9, 1250.00, 'verified', NOW());

-- Insert Sample Bookings
INSERT INTO bookings (customer_id, worker_id, service_title, service_description, booking_date, booking_time, estimated_price, booking_status, customer_location, created_at) VALUES
-- Pending jobs for Mike Worker
(1, 3, 'iPhone Screen Repair', 'My iPhone 12 screen is cracked and needs replacement', '2024-01-15', '10:00:00', 150.00, 'pending', 'East Legon, Accra', NOW()),
(2, 3, 'Laptop Not Turning On', 'Dell laptop won''t power on, need diagnosis and repair', '2024-01-16', '14:00:00', 200.00, 'pending', 'Osu, Accra', NOW()),

-- Accepted job for Mike Worker
(1, 3, 'Samsung Phone Repair', 'Samsung Galaxy S21 charging port issue', '2024-01-14', '09:00:00', 120.00, 'accepted', 'Tema, Accra', NOW()),

-- In Progress job for Grace Electrician
(2, 4, 'Ceiling Fan Installation', 'Install 3 ceiling fans in living room and bedrooms', '2024-01-13', '08:00:00', 300.00, 'in_progress', 'Cantonments, Accra', NOW()),

-- Completed jobs
(1, 3, 'MacBook Screen Replacement', 'MacBook Pro 2019 screen replacement', '2024-01-10', '11:00:00', 500.00, 'completed', 'Airport Residential, Accra', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 4, 'Home Rewiring', 'Complete rewiring of 3-bedroom apartment', '2024-01-08', '07:00:00', 1500.00, 'completed', 'Dzorwulu, Accra', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1, 4, 'Socket Installation', 'Install 5 new power sockets in office', '2024-01-05', '13:00:00', 250.00, 'completed', 'Ridge, Accra', DATE_SUB(NOW(), INTERVAL 8 DAY));

-- Update worker stats based on completed jobs
UPDATE worker_profiles 
SET total_jobs_completed = (SELECT COUNT(*) FROM bookings WHERE worker_id = 3 AND booking_status = 'completed'),
    available_balance = (SELECT COALESCE(SUM(estimated_price * 0.93), 0) FROM bookings WHERE worker_id = 3 AND booking_status = 'completed')
WHERE user_id = 3;

UPDATE worker_profiles 
SET total_jobs_completed = (SELECT COUNT(*) FROM bookings WHERE worker_id = 4 AND booking_status = 'completed'),
    available_balance = (SELECT COALESCE(SUM(estimated_price * 0.93), 0) FROM bookings WHERE worker_id = 4 AND booking_status = 'completed')
WHERE user_id = 4;

-- Verify the data
SELECT 'Users Created:' as Info;
SELECT user_id, customer_name, customer_email, user_role FROM user WHERE user_id IN (1,2,3,4,5);

SELECT 'Worker Profiles:' as Info;
SELECT user_id, skills, hourly_rate, total_jobs_completed, average_rating, available_balance FROM worker_profiles WHERE user_id IN (3,4);

SELECT 'Bookings Created:' as Info;
SELECT booking_id, customer_id, worker_id, service_title, booking_status, estimated_price FROM bookings ORDER BY booking_id DESC LIMIT 10;
