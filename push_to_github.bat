@echo off
echo ============================================
echo Push Project to GitHub
echo ============================================
echo.

REM Get current directory
cd /d "%~dp0"

REM Check if .git exists
if not exist ".git" (
    echo Git repository not initialized. Initializing...
    git init
    git add .
    git commit -m "Initial commit: Complete Student Result Management System"
)

REM Show current remote
echo Current git remote configuration:
git remote -v
echo.

echo ============================================
echo Instructions:
echo ============================================
echo 1. Go to https://github.com and create a new repository
echo 2. Copy the repository URL (HTTPS or SSH)
echo 3. Run one of these commands:
echo.
echo    For HTTPS:
echo    git remote add origin https://github.com/YOUR_USERNAME/REPO_NAME.git
echo.
echo    For SSH:
echo    git remote add origin git@github.com:YOUR_USERNAME/REPO_NAME.git
echo.
echo 4. Then push:
echo    git branch -M main
echo    git push -u origin main
echo.
echo ============================================
echo Current Status:
echo ============================================
git status
echo.
echo ============================================
echo Recent Commits:
echo ============================================
git log --oneline -5
echo.

pause

