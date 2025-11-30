# Worker Dashboard Testing Guide

## ✅ What's Been Implemented

### Worker Job Management
- Accept/Reject pending bookings
- Start accepted jobs
- Complete jobs with photo upload
- Automatic status transition validation
- Worker ownership verification
- Escrow payment scheduling

## 🧪 Test Credentials

**Worker Account:**
- Email: `worker@test.com`
- Password: `password123`

**Customer Account:**
- Email: `customer@test.com`
- Password: `password123`

## 📋 Testing Steps

### Test 1: Accept a Booking

1. Login as worker (`worker@test.com`)
2. Go to Worker Dashboard
3. Find the pending booking (Booking #25)
4. Click the **"Accept"** button
5. ✅ Status should change to "accepted"
6. ✅ Button should change to "Start Job"

### Test 2: Start a Job

1. With the accepted booking from Test 1
2. Click the **"Start Job"** button
3. ✅ Status should change to "in_progress"
4. ✅ Button should change to "Complete Job"

### Test 3: Complete a Job with Photo

1. With the in-progress booking from Test 2
2. Click the **"Complete Job"** button
3. Upload a photo (JPG, PNG, or WEBP under 5MB)
4. ✅ Photo should upload successfully
5. ✅ Status should change to "completed"
6. ✅ Success message: "Job completed successfully! Payment will be released in 24 hours."
7. ✅ Worker's total jobs completed should increase by 1

### Test 4: Reject a Booking

1. Create another test booking: `php create_test_booking.php`
2. Login as worker
3. Find the new pending booking
4. Click the **"Reject"** button
5. ✅ Status should change to "rejected"
6. ✅ No more action buttons should appear

### Test 5: Invalid Status Transitions

Try these to verify validation works:

1. **Cannot skip steps:**
   - Pending → Complete (should fail)
   - Accepted → Complete (should fail)

2. **Cannot go backwards:**
   - Completed → Accepted (should fail)
   - In Progress → Pending (should fail)

3. **Cannot modify other worker's bookings:**
   - Login as different worker
   - Try to update another worker's booking (should fail)

## 🔍 What to Check

### In the UI:
- ✅ Buttons appear based on current status
- ✅ Buttons are disabled during API calls
- ✅ Success/error messages display correctly
- ✅ Status updates in real-time
- ✅ Photo upload shows progress

### In the Database:
```sql
-- Check booking status
SELECT booking_id, booking_reference, booking_status, completion_photos 
FROM bookings 
WHERE booking_id = 25;

-- Check worker stats
SELECT user_id, total_jobs_completed, available_balance 
FROM worker_profiles 
WHERE user_id = 4;

-- Check escrow release date
SELECT booking_id, escrow_status, auto_release_date 
FROM payments 
WHERE booking_id = 25;
```

### In the Logs:
Check `error_log` for:
- Status change confirmations
- Photo upload confirmations
- Escrow release scheduling

## 🐛 Common Issues

### Photo Upload Fails
- Check `uploads/completions/` directory exists
- Check directory permissions (should be 755)
- Check file size (max 5MB)
- Check file type (JPG, PNG, WEBP only)

### Buttons Don't Work
- Check browser console for JavaScript errors
- Verify you're logged in as a worker
- Check that booking belongs to logged-in worker

### Status Won't Update
- Check current status allows the transition
- Verify booking ownership
- Check database connection

## 📊 Expected Results

After completing all tests:

1. **Booking #25:**
   - Status: `completed`
   - Has completion photo in `uploads/completions/`
   - Photo URL stored in database

2. **Worker Profile:**
   - `total_jobs_completed` increased by 1
   - `available_balance` unchanged (escrow not released yet)

3. **Payment Record:**
   - `escrow_status`: `held`
   - `auto_release_date`: 24 hours from completion

4. **New Booking:**
   - Status: `rejected`
   - No completion photo

## 🎯 Success Criteria

✅ All status transitions work correctly
✅ Photo upload works and stores file
✅ Worker can only update their own bookings
✅ Invalid transitions are blocked
✅ Escrow release is scheduled correctly
✅ Worker stats update properly
✅ UI updates in real-time

## 🚀 Next Steps

Once testing is complete, you can:
1. Implement customer booking flow
2. Add payout account management
3. Create withdrawal request system
4. Build admin payout processing
5. Implement dispute resolution

---

**Need to create more test bookings?**
Run: `php create_test_booking.php`

**Need to check worker bookings?**
Run: `php test_worker_functions.php`
