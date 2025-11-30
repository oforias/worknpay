# InfinityFree Deployment Guide

## Common "Website Not Published" Issues

If InfinityFree says your website hasn't been published, check these common issues:

### 1. **Files in Wrong Directory** ⚠️ MOST COMMON

InfinityFree requires files to be in the **`htdocs`** folder (not `public_html`).

**Solution:**
- Log into InfinityFree Control Panel
- Go to **File Manager**
- Make sure all your files are in the **`htdocs`** folder
- Your `index.php` must be directly in `htdocs/` (not in a subfolder)

**Correct Structure:**
```
htdocs/
  ├── index.php          ← Must be here!
  ├── actions/
  ├── classes/
  ├── controllers/
  ├── view/
  ├── settings/
  ├── css/
  ├── js/
  ├── db/
  └── uploads/
```

### 2. **Missing index.php in Root**

InfinityFree looks for `index.php`, `index.html`, or `index.htm` in the root `htdocs` folder.

**Check:**
- Is `index.php` in `htdocs/` (not in a subfolder)?
- Does the file exist and have content?

### 3. **Database Not Configured**

If database connection fails, the site won't load properly.

**Steps:**
1. In InfinityFree Control Panel, go to **MySQL Databases**
2. Create a new database (note the database name)
3. Create a database user (note username and password)
4. Note the database host (usually `sqlXXX.epizy.com` or `localhost`)

**Update `settings/db_cred.php`:**
```php
define("SERVER", "sqlXXX.epizy.com");  // Your database host from InfinityFree
define("USERNAME", "epiz_xxxxx");      // Your database username
define("PASSWD", "your_password");     // Your database password
define("DATABASE", "epiz_xxxxx_worknpay"); // Your database name
```

### 4. **APP_BASE_URL Not Set**

The application needs to know its base URL for callbacks.

**Update `settings/core.php` or create `settings/app_config.php`:**

Find this line in `settings/core.php`:
```php
define('APP_BASE_URL', 'http://localhost');
```

Change it to your InfinityFree domain:
```php
define('APP_BASE_URL', 'https://yourdomain.epizy.com');
// OR if you have a custom domain:
define('APP_BASE_URL', 'https://yourdomain.com');
```

### 5. **File Permissions**

Some folders need write permissions.

**In File Manager, set permissions:**
- `uploads/` → 755
- `uploads/completion_photos/` → 755
- `uploads/profile_photos/` → 755

### 6. **Database Not Imported**

The database schema must be imported.

**Steps:**
1. Go to **phpMyAdmin** in InfinityFree Control Panel
2. Select your database
3. Click **Import** tab
4. Choose `db/dbforlab.sql` file
5. Click **Go**

### 7. **SSL Certificate Not Enabled**

InfinityFree requires SSL for HTTPS.

**Steps:**
1. Go to **SSL/TLS** in Control Panel
2. Enable **Free SSL Certificate**
3. Wait a few minutes for activation
4. Update `APP_BASE_URL` to use `https://`

### 8. **Domain Not Pointing Correctly**

If using a custom domain:
- Make sure DNS is pointing to InfinityFree servers
- Wait 24-48 hours for DNS propagation

## Step-by-Step Deployment Checklist

- [ ] All files uploaded to `htdocs/` folder
- [ ] `index.php` is in `htdocs/` root (not in subfolder)
- [ ] Database created in InfinityFree
- [ ] Database credentials updated in `settings/db_cred.php`
- [ ] Database schema imported via phpMyAdmin (`db/dbforlab.sql`)
- [ ] `APP_BASE_URL` updated in `settings/core.php` to your domain
- [ ] Paystack keys configured in `settings/paystack_config.php`
- [ ] File permissions set for `uploads/` folders (755)
- [ ] SSL certificate enabled
- [ ] Test by visiting your domain

## Testing Your Deployment

1. **Visit your domain:** `https://yourdomain.epizy.com`
2. **Check for errors:** Look for PHP errors or blank pages
3. **Test database connection:** Try to register a new user
4. **Check file uploads:** Try uploading a profile photo

## Common Error Messages

### "Website Not Published"
- Files not in `htdocs/` folder
- Missing `index.php` in root
- Domain not configured

### "Database Connection Failed"
- Wrong database credentials
- Database host incorrect
- Database not created

### "500 Internal Server Error"
- PHP syntax error
- File permissions wrong
- Missing required files

### "404 Not Found"
- Files in wrong location
- `.htaccess` issues (if using)
- Path issues in code

## Need Help?

1. Check InfinityFree Control Panel → **Error Logs**
2. Enable error display temporarily in `settings/core.php`:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```
3. Check browser console for JavaScript errors
4. Verify all files uploaded correctly (compare file count)

## Important Notes

- InfinityFree has some limitations (file size, execution time)
- Free hosting may have downtime
- Consider upgrading for production use
- Always backup your database before making changes

