# Available for Payout - Dynamic Balance Explanation

## Question
Is the "Available for Payout" amount static or dynamic?

## Answer
**The balance is DYNAMIC** - it updates automatically based on payment releases and payout requests.

## How It Works

### Data Source
The "Available for Payout" amount comes from:
```sql
SELECT available_balance FROM worker_profiles WHERE user_id = $worker_id
```

### Balance Updates

#### 1. Money Added (Escrow Release)
When a job is completed and escrow is released, the balance increases:

**File:** `classes/payment_class.php` (line 193)
```php
UPDATE worker_profiles 
SET available_balance = available_balance + $worker_payout 
WHERE user_id = $worker_id
```

**When this happens:**
- Automatically after 24 hours (requires cron job setup)
- Manually by running `php release_escrow_manual.php`

#### 2. Money Subtracted (Payout Request)
When a worker requests a payout, the balance decreases:

**File:** `actions/request_payout.php`
```php
UPDATE worker_profiles 
SET available_balance = available_balance - $amount 
WHERE user_id = $worker_id
```

## Current System Flow

### For Completed Jobs:
1. Customer pays → Money goes into escrow (`payments.escrow_status = 'held'`)
2. Worker completes job → Uploads completion photo
3. After 24 hours (or manual release) → Escrow released
4. Worker's `available_balance` increases by `worker_payout` amount
5. Worker can now request payout from available balance

### Balance Components:
- **Available Balance**: Money worker can withdraw right now
- **Pending in Escrow**: Money from completed jobs waiting to be released (24 hour hold)
- **Total**: Available + Pending = Total money worker has earned

## Display in Worker Dashboard

**File:** `view/worker_dashboard_new.php` (line 1295)
```php
<div class="payout-amount">
    GH₵<?php echo number_format($stats['available_balance'] ?? 0, 2); ?>
</div>
```

The dashboard also shows:
- Pending balance (money in escrow)
- Total earnings
- Jobs completed

## Testing the System

### Check Current Balances:
```bash
php check_worker_balance.php
```

### Release Pending Escrow:
```bash
php release_escrow_manual.php
```

### Check Escrow Status:
```bash
php check_escrow_status.php
```

## Test Results
Current worker balances (from test):
- **Mike Worker**: GH₵758.20 available
- **Grace Electrician**: GH₵4,707.50 available

These amounts are dynamic and will:
- ✅ Increase when escrow is released
- ✅ Decrease when payouts are requested
- ✅ Update in real-time on the dashboard

## Production Setup
For automatic escrow release in production, set up a cron job:
```bash
# Run every hour to check and release eligible escrow payments
0 * * * * php /path/to/release_escrow_manual.php
```

## Conclusion
The "Available for Payout" balance is **100% dynamic** and reflects the actual money available for the worker to withdraw at any given moment.
