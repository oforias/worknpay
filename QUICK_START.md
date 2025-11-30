# ⚡ Quick Start Guide

## 🚀 Deploy in 3 Steps (30 minutes)

### Step 1: Get Free Hosting (5 minutes)
1. Go to https://infinityfree.net
2. Click "Sign Up"
3. Choose subdomain: `yourname.infinityfreeapp.com`
4. Create account
5. Access cPanel

### Step 2: Setup Database (10 minutes)
1. In cPanel, go to "MySQL Databases"
2. Create database: `worknpay`
3. Create user with password
4. Assign user to database
5. Go to phpMyAdmin
6. Import `db/dbforlab.sql`
7. Note: host, username, password, database name

### Step 3: Upload & Configure (15 minutes)
1. In cPanel, go to "File Manager"
2. Navigate to `htdocs` folder
3. Upload all project files
4. Edit `settings/db_cred.php`:
   ```php
   define("SERVER", "sql123.infinityfree.com"); // From cPanel
   define("USERNAME", "your_db_user");
   define("PASSWD", "your_db_password");
   define("DATABASE", "your_db_name");
   ```
5. Edit `settings/paystack_config.php`:
   ```php
   define('PAYSTACK_SECRET_KEY', 'sk_test_your_key');
   define('PAYSTACK_PUBLIC_KEY', 'pk_test_your_key');
   ```
6. Visit your site!

## ✅ Done!

Your site is now live at: `https://yourname.infinityfreeapp.com`

---

## 🐙 Push to GitHub (5 minutes)

```bash
cd C:\xampp\htdocs\payment_sample
git init
git add .
git commit -m "Initial commit - WorkNPay v1.0"
git remote add origin https://github.com/YOUR_USERNAME/worknpay.git
git push -u origin main
```

---

## 🧪 Test Your Site

1. **Create Admin Account**
   - Run `reset_admin_password.php` once
   - Login at `/view/login.php`

2. **Test as Customer**
   - Register new account
   - Browse workers
   - Book a service

3. **Test as Worker**
   - Register as worker
   - Complete profile
   - Add services

4. **Test Payments**
   - Use test card: `4111 1111 1111 1111`
   - Expiry: Any future date
   - CVV: 123
   - OTP: 123456

---

## 📞 Need Help?

- **Hosting Issues**: Check `FREE_HOSTING_GUIDE.md`
- **GitHub Issues**: Check `GITHUB_DEPLOYMENT.md`
- **General Setup**: Check `DEPLOYMENT_READY.md`
- **Full Docs**: Check `README.md`

---

## 🎯 What's Next?

- [ ] Deploy to free hosting
- [ ] Test all features
- [ ] Get Paystack live keys
- [ ] Switch to production mode
- [ ] Get custom domain (optional)
- [ ] Launch officially!

---

**Time**: 30 minutes
**Cost**: $0
**Difficulty**: Easy

🚀 **Let's Go!**
