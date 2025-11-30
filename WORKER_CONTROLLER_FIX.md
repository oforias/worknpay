# Worker Controller mysqli Error Fix

## Error
```
Fatal error: Uncaught TypeError: mysqli_real_escape_string(): Argument #1 ($mysql) must be of type mysqli, bool given in C:\xampp\htdocs\payment_sample\controllers\worker_controller.php:30
```

## Root Cause
The `worker_controller.php` was trying to use `mysqli_real_escape_string()` with `$db->db_connect()` as the first argument. However, `db_connect()` returns a boolean `true` on success, not the mysqli connection object.

## Issues Fixed

### 1. Incorrect mysqli_real_escape_string Usage
**Before:**
```php
$category_escaped = mysqli_real_escape_string($db->db_connect(), $category);
$keyword_escaped = mysqli_real_escape_string($db->db_connect(), $keyword);
```

**After:**
```php
$category_escaped = $db->db_escape($category);
$keyword_escaped = $db->db_escape($keyword);
```

The `db_class` provides a `db_escape()` method that properly escapes strings using the internal mysqli connection.

### 2. Incorrect Column Names
**Before:**
```php
u.user_contact  // Column doesn't exist
wp.years_experience  // Wrong column name
```

**After:**
```php
u.user_phone  // Correct column name
wp.experience_years  // Correct column name
```

## Changes Made

### In `get_workers_by_category_ctr()`:
- Changed `mysqli_real_escape_string($db->db_connect(), $category)` to `$db->db_escape($category)`
- Changed `user_contact` to `user_phone`
- Changed `years_experience` to `experience_years`

### In `search_workers_ctr()`:
- Changed `mysqli_real_escape_string($db->db_connect(), $keyword)` to `$db->db_escape($keyword)`
- Changed `user_contact` to `user_phone`
- Changed `years_experience` to `experience_years`

### In `get_worker_by_id_ctr()`:
- Changed `user_contact` to `user_phone`
- Changed `years_experience` to `experience_years`

## Files Modified
- `controllers/worker_controller.php` - Fixed mysqli usage and column names

## Testing
The browse workers page should now work correctly:
1. Navigate to browse_workers.php
2. Click on category filters (Electrical, Plumbing, etc.)
3. Use the search functionality
4. All queries should execute without errors

## Database Schema Reference
Correct column names:
- `users.user_phone` (not user_contact)
- `worker_profiles.experience_years` (not years_experience)
