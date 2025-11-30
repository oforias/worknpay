# GitHub Deployment Guide

## 🚀 Quick Start - Push to GitHub

### Step 1: Initialize Git Repository

```bash
cd C:\xampp\htdocs\payment_sample
git init
git add .
git commit -m "Initial commit - WorkNPay v1.0"
```

### Step 2: Create GitHub Repository

1. Go to https://github.com
2. Click "New repository"
3. Name: `worknpay`
4. Description: "Service marketplace platform for Ghana's informal sector"
5. Choose: Public or Private
6. **Don't** initialize with README (we already have one)
7. Click "Create repository"

### Step 3: Push to GitHub

```bash
git remote add origin https://github.com/YOUR_USERNAME/worknpay.git
git branch -M main
git push -u origin main
```

## 📁 What Gets Pushed

### ✅ Included Files:
- All source code (`actions/`, `classes/`, `controllers/`, `view/`)
- Database schema (`db/*.sql`)
- CSS and JavaScript files
- Documentation (README.md, guides)
- Configuration examples
- .gitignore file

### ❌ Excluded Files (via .gitignore):
- `settings/db_cred.php` (database credentials)
- `settings/paystack_config.php` (API keys)
- Test files (`test_*.php`, `check_*.php`)
- Uploads folder contents
- IDE files (.vscode, .idea)
- OS files (.DS_Store)
- Logs and temporary files

## 🔐 Security - IMPORTANT!

### Before Pushing:

1. **Check .gitignore is working:**
   ```bash
   git status
   ```
   Verify sensitive files are NOT listed

2. **Never commit:**
   - Database passwords
   - Paystack API keys
   - User uploads
   - Test accounts with real data

3. **Create example config files:**
   
   Already created for you:
   - `settings/db_cred.example.php`
   - `settings/paystack_config.example.php`

## 📝 After Pushing to GitHub

### Update Repository Settings:

1. **Add Description**
   - Go to repository settings
   - Add: "Service marketplace connecting customers with workers in Ghana"

2. **Add Topics**
   - php
   - mysql
   - paystack
   - marketplace
   - escrow
   - ghana
   - service-platform

3. **Add README Badges** (Optional)
   ```markdown
   ![PHP](https://img.shields.io/badge/PHP-8.0-blue)
   ![MySQL](https://img.shields.io/badge/MySQL-8.0-orange)
   ![License](https://img.shields.io/badge/License-MIT-green)
   ```

4. **Enable Issues**
   - Settings → Features → Issues ✓

5. **Add License**
   - Add file → Create new file
   - Name: LICENSE
   - Choose template: MIT License

## 🌐 Deploy from GitHub

### Option 1: Direct from GitHub to Hosting

Many hosts support GitHub deployment:

1. **InfinityFree with GitHub:**
   - Use FTP to upload
   - Or use GitHub Actions (advanced)

2. **Heroku (Free tier):**
   ```bash
   heroku create worknpay
   heroku addons:create cleardb:ignite
   git push heroku main
   ```

3. **Vercel/Netlify:**
   - Connect GitHub repository
   - Configure build settings
   - Deploy automatically

### Option 2: Clone to Hosting

```bash
# On your hosting server
cd public_html
git clone https://github.com/YOUR_USERNAME/worknpay.git .
```

Then configure database and Paystack settings.

## 🔄 Update Workflow

### Making Changes:

```bash
# Make your changes
git add .
git commit -m "Description of changes"
git push origin main
```

### Pull Changes on Server:

```bash
# On hosting server
cd public_html
git pull origin main
```

## 📋 Deployment Checklist

- [ ] Push code to GitHub
- [ ] Verify .gitignore working
- [ ] No sensitive data in repository
- [ ] README.md complete
- [ ] License added
- [ ] Repository description set
- [ ] Topics added
- [ ] Issues enabled
- [ ] Example config files included

## 🎯 GitHub Repository Structure

```
worknpay/
├── .gitignore
├── README.md
├── LICENSE
├── DEPLOYMENT_READY.md
├── FREE_HOSTING_GUIDE.md
├── actions/
├── classes/
├── controllers/
├── css/
├── db/
│   ├── dbforlab.sql
│   └── modifications.sql
├── js/
├── settings/
│   ├── db_cred.example.php
│   ├── paystack_config.example.php
│   ├── db_class.php
│   └── core.php
├── uploads/
│   └── .gitkeep
├── view/
└── index.php
```

## 💡 Pro Tips

1. **Use Branches:**
   ```bash
   git checkout -b feature/new-feature
   # Make changes
   git commit -m "Add new feature"
   git push origin feature/new-feature
   # Create pull request on GitHub
   ```

2. **Tag Releases:**
   ```bash
   git tag -a v1.0 -m "Version 1.0 - Initial Release"
   git push origin v1.0
   ```

3. **GitHub Actions** (Advanced):
   - Auto-deploy on push
   - Run tests automatically
   - Build and deploy

4. **Protect Main Branch:**
   - Settings → Branches
   - Add rule for `main`
   - Require pull request reviews

5. **Add Collaborators:**
   - Settings → Collaborators
   - Invite team members

## 🆘 Troubleshooting

### Issue: Large files rejected

```bash
# Remove large files from git
git rm --cached path/to/large/file
echo "path/to/large/file" >> .gitignore
git commit -m "Remove large file"
```

### Issue: Sensitive data committed

```bash
# Remove from history (CAREFUL!)
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch settings/db_cred.php" \
  --prune-empty --tag-name-filter cat -- --all

# Force push
git push origin --force --all
```

### Issue: Merge conflicts

```bash
# Pull latest changes
git pull origin main

# Resolve conflicts in files
# Then:
git add .
git commit -m "Resolve merge conflicts"
git push origin main
```

## 📞 Need Help?

- GitHub Docs: https://docs.github.com
- Git Basics: https://git-scm.com/book/en/v2
- GitHub Desktop: https://desktop.github.com (GUI alternative)

---

**Ready to push?** Follow Step 1-3 above and your code will be on GitHub in minutes! 🚀
