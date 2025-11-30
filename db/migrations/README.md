# Database Migrations

This directory contains database migration scripts for the WorkNPay platform.

## Migration 001: Make service_id Nullable

**Purpose**: Allow bookings to be created without a specific service reference, making the service_id field optional.

**Requirements**: 5.4, 5.5

**Date**: 2025-11-24

### What Changed

- Modified `bookings.service_id` column from `NOT NULL` to `NULL`
- Updated foreign key constraint to `ON DELETE SET NULL`
- Bookings can now be created without a service_id

### How to Apply

#### Option 1: Using PHP Script (Recommended)
```bash
php db/apply_migration.php
```

Or with full XAMPP path:
```bash
C:\xampp\php\php.exe db/apply_migration.php
```

#### Option 2: Using MySQL Command Line
```bash
mysql -u root -h localhost worknpay < db/migrations/001_make_service_id_nullable.sql
```

Or with full XAMPP path:
```bash
C:\xampp\mysql\bin\mysql.exe -u root -h localhost worknpay < db/migrations/001_make_service_id_nullable.sql
```

#### Option 3: Using phpMyAdmin
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select the `worknpay` database
3. Go to the SQL tab
4. Copy and paste the contents of `001_make_service_id_nullable.sql`
5. Click "Go" to execute

### Verification

After applying the migration, verify it was successful:

```sql
SELECT COLUMN_NAME, IS_NULLABLE, COLUMN_TYPE 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'worknpay' 
  AND TABLE_NAME = 'bookings' 
  AND COLUMN_NAME = 'service_id';
```

Expected result:
- `IS_NULLABLE` should be `YES`
- `COLUMN_TYPE` should be `int(11)`

### Rollback

If you need to revert this migration:

```bash
php db/apply_migration.php rollback
```

Or manually:
```bash
mysql -u root -h localhost worknpay < db/migrations/001_make_service_id_nullable_rollback.sql
```

**WARNING**: Rollback will fail if any bookings have `NULL` service_id. You must either delete those bookings or update them with a valid service_id before rolling back.

### Impact

**Before Migration**:
- All bookings required a valid service_id
- Attempting to create a booking without a service caused: "Failed to create booking in database"

**After Migration**:
- Bookings can be created with or without a service_id
- NULL service_id is allowed and valid
- Foreign key constraint still enforces referential integrity when service_id is provided

### Testing

Test the migration by creating a booking without a service_id:

```php
$booking = new Booking();
$result = $booking->create_booking(
    customer_id: 1,
    worker_id: 2,
    service_id: null,  // NULL is now allowed
    booking_date: '2025-11-25',
    booking_time: '14:00:00',
    service_address: '123 Main St',
    estimated_price: 150.00
);
```

Expected: Booking should be created successfully with `service_id = NULL`.

## Migration History

| Migration | Date | Description | Status |
|-----------|------|-------------|--------|
| 001 | 2025-11-24 | Make service_id nullable | ✓ Applied |

## Future Migrations

Add new migrations here following the naming convention:
- `XXX_descriptive_name.sql` - Forward migration
- `XXX_descriptive_name_rollback.sql` - Rollback script
- Update this README with migration details
