# GitHub Repository Setup Guide

## Step 1: Create GitHub Repository

1. Go to [GitHub.com](https://github.com) and sign in
2. Click the **"+"** icon in the top right → **"New repository"**
3. Repository name: `Student-Result-Management-System` (or your preferred name)
4. Description: `Complete Student Result Management System with role-based authentication`
5. Choose **Public** or **Private**
6. **DO NOT** initialize with README, .gitignore, or license (we already have these)
7. Click **"Create repository"**

## Step 2: Push to GitHub

After creating the repository, GitHub will show you commands. Use these commands:

### Option A: If repository is empty (recommended)

```bash
# Navigate to your project directory
cd "d:\github project\Student-Result-Management-System-1"

# Add remote repository (replace YOUR_USERNAME with your GitHub username)
git remote add origin https://github.com/YOUR_USERNAME/Student-Result-Management-System.git

# Push to GitHub
git branch -M main
git push -u origin main
```

### Option B: Using SSH (if you have SSH keys set up)

```bash
git remote add origin git@github.com:YOUR_USERNAME/Student-Result-Management-System.git
git branch -M main
git push -u origin main
```

## Step 3: Verify

1. Refresh your GitHub repository page
2. You should see all your files uploaded
3. The README.md will be displayed automatically

## Troubleshooting

### Authentication Required
If asked for credentials:
- Use a **Personal Access Token** instead of password
- Generate token: GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
- Or use GitHub CLI: `gh auth login`

### Repository Already Exists Error
If the remote already exists:
```bash
git remote set-url origin https://github.com/YOUR_USERNAME/Student-Result-Management-System.git
git push -u origin main
```

### Large File Warning
If you get file size warnings, check that `.gitignore` is working properly.

---

**After pushing, your repository will be live at:**
`https://github.com/YOUR_USERNAME/Student-Result-Management-System`

