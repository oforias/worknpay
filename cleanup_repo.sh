#!/bin/bash
# GitHub Repository Cleanup Script
# This script removes non-essential files from Git tracking

echo "=== GitHub Repository Cleanup Script ==="
echo ""
echo "This script will remove non-essential files from Git tracking."
echo "Files will be removed from Git but kept locally (unless you choose to delete them)."
echo ""
read -p "Continue? (y/n): " confirm

if [ "$confirm" != "y" ]; then
    echo "Aborted."
    exit 1
fi

echo ""
echo "Step 1: Removing test files..."
git rm --cached test_*.php 2>/dev/null
git rm --cached check_*.php 2>/dev/null
git rm --cached create_test_*.php 2>/dev/null
git rm --cached debug_*.php 2>/dev/null
git rm --cached setup_test_*.php 2>/dev/null
git rm --cached show_test_*.php 2>/dev/null
git rm --cached verify_*.php 2>/dev/null
git rm --cached release_escrow_manual.php 2>/dev/null
git rm --cached reset_admin_password.php 2>/dev/null
git rm --cached generate_adf_documents.php 2>/dev/null
git rm --cached setup_worker_data.php 2>/dev/null
git rm --cached actions/test_verify_payment.php 2>/dev/null
git rm --cached view/debug_session.php 2>/dev/null
git rm --cached test_results.html 2>/dev/null

echo "Step 2: Removing documentation files (except README.md)..."
# Remove all .md files except README.md
find . -name "*.md" -not -name "README.md" -not -path "./.git/*" -exec git rm --cached {} \; 2>/dev/null

echo "Step 3: Removing ADF_SUBMISSION folder..."
git rm -r --cached ADF_SUBMISSION/ 2>/dev/null

echo "Step 4: Removing database test files and migrations..."
git rm --cached db/test_*.sql 2>/dev/null
git rm -r --cached db/migrations/ 2>/dev/null
git rm --cached db/apply_*.php 2>/dev/null
git rm --cached db/ensure_*.php 2>/dev/null
git rm --cached db/run_*.php 2>/dev/null
git rm --cached db/MIGRATION_STATUS.md 2>/dev/null
git rm --cached db/modifications.sql 2>/dev/null

echo "Step 5: Ensuring uploads folder structure..."
# Create .gitkeep files if they don't exist
mkdir -p uploads/completion_photos
mkdir -p uploads/profile_photos
touch uploads/.gitkeep
touch uploads/completion_photos/.gitkeep
touch uploads/profile_photos/.gitkeep
git add uploads/.gitkeep uploads/completion_photos/.gitkeep uploads/profile_photos/.gitkeep 2>/dev/null

echo "Step 6: Removing uploads content (keeping structure)..."
git rm -r --cached uploads/completions/ 2>/dev/null
find uploads -type f ! -name ".gitkeep" -exec git rm --cached {} \; 2>/dev/null

echo ""
echo "=== Cleanup Complete ==="
echo ""
echo "Next steps:"
echo "1. Review changes: git status"
echo "2. Commit: git commit -m 'Clean up: Remove non-essential files'"
echo "3. Push to GitHub: git push origin main --force"
echo ""
echo "⚠️  WARNING: The --force flag will overwrite your GitHub repository!"
echo "   Make sure you have a backup before pushing!"

