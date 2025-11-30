# Worker Profile "Worker Not Found" Fix

## Issue
When customers tapped on a worker from the customer dashboard (home.php), they were getting a "worker not found" error.

## Root Cause
The `worker_profile.php` file had incorrect column names in the SQL query:
1. Used `user_contact` instead of `user_phone` (column doesn't exist)
2. Used `years_experience` instead of `experience_years` (wrong column name)

These incorrect column names caused the SQL query to fail, resulting in no worker data being returned.

## Changes Made

### Fixed SQL Query in view/worker_profile.php

**Before:**
```php
$worker_query = "SELECT u.user_id, u.user_name, u.user_email, u.user_city, u.user_contact,
                 wp.bio, wp.skills, wp.hourly_rate, wp.average_rating, wp.total_jobs_completed, 
                 wp.verification_badge, wp.years_experience
                 FROM users u
                 LEFT JOIN worker_profiles wp ON u.user_id = wp.user_id
                 WHERE u.user_id = $worker_id AND u.user_role = 2 AND u.is_active = 1";
```

**After:**
```php
$worker_query = "SELECT u.user_id, u.user_name, u.user_email, u.user_city, u.user_phone,
                 wp.bio, wp.skills, wp.hourly_rate, wp.average_rating, wp.total_jobs_completed, 
                 wp.verification_badge, wp.experience_years
                 FROM users u
                 LEFT JOIN worker_profiles wp ON u.user_id = wp.user_id
                 WHERE u.user_id = $worker_id AND u.user_role = 2 AND u.is_active = 1";
```

### Fixed Display Variable

**Before:**
```php
<?php if ($worker['years_experience']): ?>
    <span><?php echo $worker['years_experience']; ?> years experience</span>
<?php endif; ?>
```

**After:**
```php
<?php if ($worker['experience_years']): ?>
    <span><?php echo $worker['experience_years']; ?> years experience</span>
<?php endif; ?>
```

## Test Results
✅ Worker "Mike Worker" (ID: 4) - Profile loads successfully
✅ Worker "Grace Electrician" (ID: 5) - Profile loads successfully

All worker data now displays correctly:
- Name, email, phone, city
- Skills and bio
- Hourly rate
- Rating and jobs completed
- Years of experience
- Verification badge

## Verification Steps
1. Log in as a customer (customer@test.com / password123)
2. Go to Home page
3. Click on any worker card in the "Featured Workers" section
4. Worker profile should load with all details
5. "Book Now" button should work correctly

## Files Modified
- `view/worker_profile.php` - Fixed SQL query column names
- `test_worker_profile.php` - Created test script (can be deleted after verification)

## Database Schema Reference
Correct column names from schema:
- `users.user_phone` (not user_contact)
- `worker_profiles.experience_years` (not years_experience)
