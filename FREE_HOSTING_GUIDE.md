# Free Hosting Guide for WorkNPay

## 🚀 Best Free Hosting Options for PHP + MySQL

### Option 1: InfinityFree (Recommended)
**Best for: Production-ready free hosting**

✅ **Features:**
- Unlimited bandwidth
- Unlimited disk space
- MySQL databases
- PHP 7.4/8.0 support
- Free SSL certificates
- cPanel access
- No ads
- 99% uptime

📝 **Setup Steps:**
1. Go to https://infinityfree.net
2. Click "Sign Up"
3. Choose a subdomain (e.g., worknpay.infinityfreeapp.com) or use your own domain
4. Create account
5. Access cPanel
6. Create MySQL database:
   - Go to MySQL Databases
   - Create database: `worknpay`
   - Create user and password
   - Assign user to database
7. Upload files:
   - Use File Manager or FTP
   - Upload all files to `htdocs` folder
8. Import database:
   - Go to phpMyAdmin
   - Select your database
   - Import `db/dbforlab.sql`
9. Update `settings/db_cred.php`:
   ```php
   define("SERVER", "sqlXXX.infinityfree.com"); // From cPanel
   define("USERNAME", "your_db_user");
   define("PASSWD", "your_db_password");
   define("DATABASE", "your_db_name");
   ```
10. Visit your site!

---

### Option 2: 000webhost
**Best for: Quick deployment**

✅ **Features:**
- 300 MB disk space
- 3 GB bandwidth
- 1 MySQL database
- PHP 7.4/8.0
- Free SSL
- No ads
- Website builder

📝 **Setup Steps:**
1. Go to https://www.000webhost.com
2. Sign up for free
3. Create new website
4. Choose subdomain or custom domain
5. Access File Manager
6. Upload files to `public_html`
7. Create MySQL database in control panel
8. Import database via phpMyAdmin
9. Update database credentials
10. Done!

---

### Option 3: Awardspace
**Best for: Reliable uptime**

✅ **Features:**
- 1 GB disk space
- Unlimited bandwidth
- 1 MySQL database
- PHP 8.0 support
- Free SSL
- No ads
- 99.9% uptime

📝 **Setup Steps:**
1. Go to https://www.awardspace.com
2. Sign up for free hosting
3. Choose subdomain
4. Access control panel
5. Create MySQL database
6. Upload files via FTP or File Manager
7. Import database
8. Update credentials
9. Launch!

---

### Option 4: FreeHosting.com
**Best for: No restrictions**

✅ **Features:**
- 10 GB disk space
- Unlimited bandwidth
- Unlimited MySQL databases
- PHP 7.4/8.0
- Free SSL
- cPanel

📝 **Setup Steps:**
1. Go to https://www.freehosting.com
2. Sign up
3. Choose plan (free)
4. Set up domain/subdomain
5. Access cPanel
6. Create database
7. Upload files
8. Import database
9. Configure
10. Go live!

---

## 📦 Deployment Checklist

### Before Uploading:

- [ ] **Test locally** - Ensure everything works
- [ ] **Export database** - Use phpMyAdmin to export
- [ ] **Update Paystack keys** - Use live keys (not test)
- [ ] **Check file permissions** - uploads folder needs write access
- [ ] **Remove test files** - Delete test_*.php files
- [ ] **Update base URLs** - If using absolute paths

### Files to Upload:

```
/actions
/classes
/controllers
/css
/db (only .sql files)
/js
/settings
/uploads (empty folder)
/view
index.php
```

### Files NOT to Upload:

```
test_*.php
check_*.php
create_test_*.php
debug_*.php
.git/
.kiro/
node_modules/
```

### After Uploading:

- [ ] Import database
- [ ] Update `settings/db_cred.php`
- [ ] Test login (admin, worker, customer)
- [ ] Test booking flow
- [ ] Test payment (use Paystack test mode first)
- [ ] Test disputes
- [ ] Test services
- [ ] Check mobile responsiveness
- [ ] Enable SSL certificate
- [ ] Set up custom domain (optional)

---

## 🔧 Configuration Steps

### 1. Database Setup

```sql
-- Import in this order:
1. db/dbforlab.sql (main schema)
2. db/modifications.sql (if exists)
3. db/add_services_table.sql (services feature)
```

### 2. Update Database Credentials

Edit `settings/db_cred.php`:
```php
<?php
define("SERVER", "your_host"); // From hosting provider
define("USERNAME", "your_username");
define("PASSWD", "your_password");
define("DATABASE", "your_database");
?>
```

### 3. Paystack Configuration

Edit `settings/paystack_config.php`:
```php
// For testing
define('PAYSTACK_SECRET_KEY', 'sk_test_your_test_key');
define('PAYSTACK_PUBLIC_KEY', 'pk_test_your_test_key');

// For production (after testing)
define('PAYSTACK_SECRET_KEY', 'sk_live_your_live_key');
define('PAYSTACK_PUBLIC_KEY', 'pk_live_your_live_key');
```

### 4. File Permissions

Set these folders to writable (755 or 777):
```
/uploads
/uploads/completion_photos
/uploads/profile_photos
```

---

## 🌐 Custom Domain Setup (Optional)

### If you have a domain:

1. **Point domain to hosting:**
   - Go to your domain registrar
   - Update nameservers to hosting provider's nameservers
   - Or add A record pointing to hosting IP

2. **Add domain in hosting:**
   - Go to hosting control panel
   - Add domain/addon domain
   - Point to your files

3. **Enable SSL:**
   - Most free hosts offer free SSL
   - Enable in control panel
   - Force HTTPS redirect

---

## 🔒 Security Checklist

- [ ] Change default admin password
- [ ] Use strong database password
- [ ] Enable SSL/HTTPS
- [ ] Remove test files
- [ ] Set proper file permissions
- [ ] Keep Paystack keys secure
- [ ] Regular database backups
- [ ] Monitor error logs

---

## 📱 Testing After Deployment

### Test These Features:

1. **User Registration & Login**
   - Customer registration
   - Worker registration
   - Admin login

2. **Worker Features**
   - Create services
   - Accept bookings
   - Upload completion photos
   - Request payouts
   - Respond to disputes

3. **Customer Features**
   - Browse workers
   - Book services
   - Make payments
   - Rate workers
   - Open disputes

4. **Admin Features**
   - View dashboard
   - Manage users
   - Resolve disputes
   - Process payouts
   - View reports

5. **Payment Flow**
   - Initialize payment
   - Paystack redirect
   - Payment verification
   - Escrow holding
   - Payout processing

---

## 🆘 Troubleshooting

### Common Issues:

**1. Database Connection Error**
- Check credentials in `settings/db_cred.php`
- Verify database exists
- Check if user has permissions

**2. Blank Page**
- Enable error reporting temporarily
- Check PHP version (needs 7.4+)
- Check file permissions

**3. Payment Not Working**
- Verify Paystack keys
- Check callback URL
- Test with Paystack test cards

**4. Images Not Uploading**
- Check uploads folder permissions (777)
- Verify folder exists
- Check PHP upload limits

**5. Sessions Not Working**
- Check session folder permissions
- Verify PHP session settings
- Clear browser cookies

---

## 💡 Pro Tips

1. **Use Git for deployment:**
   - Push to GitHub
   - Pull on server
   - Easier updates

2. **Database backups:**
   - Export weekly
   - Store locally
   - Use hosting backup features

3. **Monitor performance:**
   - Check error logs
   - Monitor uptime
   - Track load times

4. **Gradual rollout:**
   - Test with test accounts first
   - Use Paystack test mode
   - Switch to live after testing

5. **Custom domain:**
   - More professional
   - Better for business
   - Easier to remember

---

## 🎯 Recommended: InfinityFree

**Why InfinityFree is best:**
- ✅ Truly unlimited (no hidden limits)
- ✅ No ads on your site
- ✅ Professional cPanel
- ✅ Free SSL certificates
- ✅ Good uptime (99%+)
- ✅ Active community support
- ✅ Easy to upgrade to paid if needed

**Get Started:**
1. Visit https://infinityfree.net
2. Sign up (takes 2 minutes)
3. Follow setup steps above
4. Your site will be live in 30 minutes!

---

## 📞 Need Help?

If you encounter issues:
1. Check hosting provider's documentation
2. Search their support forum
3. Contact their support team
4. Check PHP error logs
5. Test locally first

---

**Status**: Ready to Deploy! 🚀
**Estimated Setup Time**: 30-60 minutes
**Cost**: $0 (Free Forever)
