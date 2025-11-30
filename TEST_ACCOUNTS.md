# 🔐 WorkNPay Test Accounts

All test accounts use the same password: **`password123`**

✅ **All passwords have been reset and verified working!**

---

## 👨‍💼 Admin Accounts (✅ TESTED & WORKING)

### Primary Admin
- **Name:** System Admin
- **Email:** `admin@worknpay.com`
- **Phone:** +233000000000
- **Role:** Admin (Full Access)
- **Password:** `password123`
- **Status:** ✅ Verified Working

### Test Admin
- **Name:** Test Admin
- **Email:** `testadmin@test.com`
- **Phone:** 0244567890
- **Role:** Admin (Full Access)
- **Password:** `password123`
- **Status:** ✅ Verified Working

---

## 🔧 Worker Accounts

### Worker 1
- **Name:** Mike Worker
- **Email:** `worker@test.com`
- **Phone:** 0244345678
- **Role:** Worker
- **Password:** `password123`
- **Skills:** General handyman services

### Worker 2
- **Name:** Grace Electrician
- **Email:** `grace@test.com`
- **Phone:** 0244456789
- **Role:** Worker
- **Password:** `password123`
- **Skills:** Electrical services

---

## 👥 Customer Accounts

### Customer 1
- **Name:** John Customer
- **Email:** `customer@test.com`
- **Phone:** 0244123456
- **Role:** Customer
- **Password:** `password123`

### Customer 2
- **Name:** Sarah Buyer
- **Email:** `sarah@test.com`
- **Phone:** 0244234567
- **Role:** Customer
- **Password:** `password123`

### Customer 3
- **Name:** Mmalebna Yin-Nongti Zumah
- **Email:** `mmalebnazumah@yahoo.com`
- **Phone:** 0509276612
- **Role:** Customer
- **Password:** `password123`

---

## 🚀 How to Login

1. Navigate to: `http://localhost/payment_sample/view/login.php`
2. Enter the email from any account above
3. Enter password: `password123`
4. Click "Login"
5. You'll be automatically redirected based on your role:
   - **Admin** → Admin Dashboard
   - **Worker** → Worker Dashboard
   - **Customer** → Customer Dashboard

---

## 🎯 Quick Access Links

- **Login Page:** `view/login.php`
- **Register:** `view/register.php`
- **Admin Dashboard:** `view/admin_dashboard.php` (requires admin login)
- **Worker Dashboard:** `view/worker_dashboard_new.php` (requires worker login)
- **Customer Dashboard:** `view/customer_dashboard.php` (requires customer login)

---

## 📝 Notes

- All passwords are hashed using PHP's `password_hash()` function
- The system automatically detects user role and redirects appropriately
- Admin accounts have full access to all features
- Worker accounts can manage jobs, view earnings, and request payouts
- Customer accounts can book services and make payments

---

## 🔒 Security Reminder

**⚠️ IMPORTANT:** These are test accounts for development only. 
In production:
- Change all passwords
- Remove test accounts
- Implement proper password policies
- Enable two-factor authentication for admin accounts
