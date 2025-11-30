# Database Migration Status

## Task 1: Create Payout Accounts Table and Update Schema

### Status: ✓ COMPLETED

All subtasks have been verified and completed:

### 1.1 Worker Payout Accounts Table Migration ✓

**File:** `db/migrations/003_create_payout_accounts.sql`

The migration file exists and includes:
- ✓ `worker_payout_accounts` table with all required fields
- ✓ Foreign key constraint to `users` table
- ✓ Indexes on `worker_id` and `is_default`
- ✓ Support for both mobile money and bank transfer accounts
- ✓ Bonus: Updates to `disputes` table for worker responses

**To Apply This Migration:**
```bash
# Navigate to the project root directory
cd C:\xampp\htdocs\payment_sample

# Run the migration script (requires PHP CLI)
php db/apply_payout_accounts_migration.php
```

Or manually import via MySQL:
```bash
mysql -u root -p worknpay_db < db/migrations/003_create_payout_accounts.sql
```

### 1.2 Uploads Directory Structure ✓

**Directory:** `uploads/completions/`

Already exists with proper security:
- ✓ Directory created with proper permissions
- ✓ `.htaccess` file prevents PHP execution and directory listing
- ✓ `index.php` blocks direct directory access
- ✓ Only image files (jpg, jpeg, png, gif, webp) are accessible

### 1.3 Bookings Table Completion Photos ✓

**Table:** `bookings`

The main schema (`db/dbforlab.sql`) already includes:
- ✓ `completion_date` column (TIMESTAMP NULL)
- ✓ `completion_photos` column (TEXT - stores JSON array of photo URLs)

Both columns are present in the base schema and ready to use.

## Next Steps

1. **Apply the payout accounts migration** if you haven't already:
   - Run `php db/apply_payout_accounts_migration.php`
   - Or manually import the SQL file

2. **Verify the migration** by checking:
   - Table exists: `SHOW TABLES LIKE 'worker_payout_accounts';`
   - Table structure: `DESCRIBE worker_payout_accounts;`

3. **Continue with the next task** in the implementation plan:
   - Task 2: Create payout management classes

## Database Schema Summary

### worker_payout_accounts Table Structure

```sql
- account_id (INT, PRIMARY KEY, AUTO_INCREMENT)
- worker_id (INT, NOT NULL, FOREIGN KEY → users.user_id)
- account_type (ENUM: 'mobile_money', 'bank_transfer')
- mobile_number (VARCHAR(15))
- mobile_network (ENUM: 'MTN', 'Vodafone', 'Telecel')
- bank_name (VARCHAR(100))
- account_number (VARCHAR(50))
- account_holder_name (VARCHAR(100))
- is_default (TINYINT(1), DEFAULT 0)
- is_verified (TINYINT(1), DEFAULT 0)
- created_at (TIMESTAMP)
- updated_at (TIMESTAMP)

Indexes:
- idx_worker_id (worker_id)
- idx_is_default (is_default)
```

### bookings Table (Relevant Columns)

```sql
- completion_date (TIMESTAMP NULL)
- completion_photos (TEXT) -- JSON array of photo URLs
```
